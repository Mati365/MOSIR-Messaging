<?php
namespace Core {
	include 		'module.php';
	include 		'config/config.php';
	include 		'user.php';
	use \BitFlag\Messages,
		\BitFlag\MessageFlag;

	/* 
	 * Aseracja z wyjątkiem, jeśli $val to false wyjątek
	 * @param 	$val 		Bool
	 * @param 	$exception 	Treść wyjątku
	 */
	function exAssert($val, $exception) {
		if(!$val || (is_array($val) && empty($val)))
			throw new \Exception($exception);
		return $val;
	}
	function arrayToObject($obj, $array) {
		foreach ($array as $key => $value)
    		$obj->$key = $value;
    	return $obj;
	}
	function isSetFlag($val, $flag) {
		return ($val & $flag) === $flag;
	}
	/* Informacje o zapytaniu */
	class QueryInfo {
		public 	$table 		= 	'';
		public 	$map 		= 	[];
		public 	$query_opts = 	'';

		public function __construct($table, $map = [], $query_opts = '') {
			$this->table  		= 	$table;
			$this->map 			= 	$map;
			$this->query_opts 	=	$query_opts;
		}
	}
	class SQLwrapper extends Module {
		private $config 		=	null;
		private $handle 		= 	null;
		private $query_count 	=	0;
		/*
		 * Getter dla handle
		 * @return MYSQLI handle
		 */
		public function getHandle() {
			return $this->handle;
		}
		public function getQueryCount() {
			return $this->query_count;
		}
		public function getConfig() {
			return $this->config;
		}
		/*
		 * Zapytania
		 */
		public function queries($queries, $array = false) {
			$results = [ ];
			foreach($queries as $val)
				array_push($results, $this->query($val, $array));
			return $results;
		}
		public function query($query, $array = false, $class = '') {
			$this->query_count++;
			$result = $this->handle->query($query);
			return $array ? self::getArray($result, $class) : $result;
		}
		public function queryObject($query, $array = false, $class = '') {
			return $this->query($query, $array, $class)
						->fetch_object();
		}
		private function safeQuery($query) {
			$result = null;
			exAssert(
				$result = $this->handle->query($query),
				'Invalid query: '.$query
			);
			return exAssert($result, 'Cannot find object!');
		}
		/* 
		 * Inicjowanie połączenia z bazą danych SQL 
 		 * @param 	$config 	Konfiguracja serwera 
		 */
		public function __construct(Config $config) {
			$this->config = $config;
			$this->handle = new \mysqli(
					$config->database['host'], 
					$config->database['user'],
					$config->database['password'],
					$config->database['database']);
    		$this->handle->set_charset("utf8");
		}
		public function init($app) {
			$this->app_exts = [
				'sql' 	 => function($self) {
					return $self->offsetGet('Core\SQLwrapper');
				},
				'config' => function($self) {
					return $self->config;
				}
			];
			parent::init($app);
		}
		/*
		 * Zbieranie obiektów użytkowników
		 * @param  	$result 	Rezultat kwerendy do bazy
		 * @return 	Tablica obiektów użytkowników
		 */
		public static function getArray($result, $class = '') {
			$objects = array();
			if(method_exists($result, 'fetch_object'))
				while ($obj = $result->fetch_object())
					$objects[] = empty($class) ? $obj : arrayToObject(new $class, $obj);
			return $objects;
		}
		/*
		 * Wybieranie czegoś z kryteriami
		 * @param 	$map 	Mapa kryteriów
		 */
		private function parseFilter($map) {
			$filter = '';
			foreach($map as $key => $val)
				$filter = "`{$key}` = '{$val}' and ";
			return empty($filter) ? '' : (' where '.substr($filter, 0, strlen($filter)-4));
		}
		public function parseUpdateQuery($table, $vals, $map) {
			$query = '';
			foreach($vals as $key => $val)
				$query .= ",`{$key}`='{$val}' ";
			return 'update '.$table.' set '.substr($query, 1).' '.($this->parseFilter($map));
		}
		/* 
		 * Zwracanie sparsowanych rezultatów
		 * @param 	$table 	Nazwa tabeli
		 * @param 	$map 	Kryteria
		 * @return 	Tablica
		 */
		public function getBy(QueryInfo $info) {
			$query = 'select * from '.
						$info->table.' '.
						$this->parseFilter($info->map).' '.
						$info->query_opts;
			return $this->safeQuery($query);
		}
		public function getRowsBy(QueryInfo $info, $class = '') {
			return self::getArray($this->getBy($info), $class);
		}
		public function getById($table, $id, $class = '') {
			$assoc = $this
				->getBy(new QueryInfo(
					$table,
					[
						'id' => $id
					], 
					' limit 1'))
				->fetch_assoc();
			if(!empty($class))
				$assoc = arrayToObject(new $class, $assoc);
			return $assoc;
		}
		/*
		 * Metody odpowiedzialne za 
		 * zarządzanie użytkownikami
		 * @param 	$map 	Lista warunków dla użytkownika
		 */
		public function getUserBy($map) {
			exAssert(
				!empty($arr = self::getRowsBy(
							new QueryInfo(Messages::USERS_TABLE, $map), 
							'\Core\User'
							)),
				'Unknown user! '
			);
			return $arr[0];
		}
		public function userExists($login) {
			return count($this->getUserBy(array(
					'login'	=> $login
				))) === 1;
		}
		public function insertUser(User $user, $crypt = true) {
			try {
				$this->getUserBy(array(
						'login'	=>	$user->login
					));
			} catch(\Exception $e) {
				if($crypt)
					$user->password = self::encryptPassword($user->password);
				$this->insertInto(Messages::USERS_TABLE, get_object_vars($user));
			}
		}
		/**
		 * Zmiana hasła użytkownika
		 * @param  User   $user     Użytkownik
		 * @param  $old_pass 		Stare hasło
		 * @param  $new_pass 		Nowe hasło
		 */
		public function changePassword($old_pass, $new_pass, User $user=null) {
			if(is_null($user))
				$user = $this->app->user();

			$old_hash = self::encryptPassword($old_pass);
			$new_hash = self::encryptPassword($new_pass);
			
			$this->query("update ".Messages::USERS_TABLE."
							set password = if(password = '{$old_hash}', '{$new_hash}', password)
							where id={$user->id}");
			return $this->handle->affected_rows;
		}
		/*
		 * Kodowanie hasła
		 * @param 	$pass 	Hasło w jawnym tekście
		 * @return 	Hash hasła
		 */
		public static function encryptPassword($pass) {
			return hash('sha256', 
						Config::SALT.hash('sha512', $pass.Config::SALT));
		}
		/*
		 * Dodawanie obiektów do bazy
		 * @param 	$table 	Tabela docelowa
		 * @param 	$values Mapa
		 */
		public function safeString($val) {
			return is_string($val)? 
						$this->handle->real_escape_string($val):
						$val;
		}
		public function insertInto($table, $values, $lambda = null) {
			$cols = '';
			$vals = '';
			foreach($values as $key => $val) {
				exAssert(
					strlen($key) && strlen($val), 
					'Cannot add object!Empty key/col:'.$key.'/'.$val);
				$cols .= ',`'.$key.'`';
      			$vals .= ",'".$val."'";
			}
			$this->handle->query(
				!isset($lambda) ?
					'insert ignore into `'.$table.'`('.substr($cols, 1).') values('.substr($vals, 1).')' :
					$lambda($table, $cols, $vals)
			);
		}
	}
}
?>