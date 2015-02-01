<?php
namespace Core {
	use \BitFlag\Messages,
		\BitFlag\SettingType,
		\BitFlag\SettingFlag;

	/* Użytkownik systemu */
	class User {
		public $id 			= 	0;
		public $login		=	'';
		public $password	=	'';		//	sha512 => sól => md5
		public $type 		=	0b0001;	//	uprawnienia
		public $name 		=	''; 	// 	imie
		public $surname 	=	''; 	// 	nazwisko
		public $job 		=	''; 	//	sprzątaczka
		public $reg_date  	=	'';
		
		public function __construct(
					$login 		=	'', 
					$password 	=	'', 
					$name 		=	'', 
					$surname 	=	'', 
					$type = \BitFlag\UserType::READER) {
			$this->login 	= $login;
			$this->password = $password;
			$this->type 	= $type;
			$this->name 	= $name;
			$this->surname 	= $surname;
		}
		/*
		 * Sprawdzenie przyzwoleń
		 * @param 	$permission 	Przyzwolenie
		 * @return 	true/false
		 */
		public function checkPermission($permission) {
			return isSetFlag($this->type, $permission);
		}
	}

	/*
	 * Ustawienia występujące tylko
	 * w parach np. dla object_id=1 to zone_id=2 lub zone_id=3
	 */
	class Pair {
		public 	$t1,$t2;

		public function __construct($t1 = null, 
									$t2 = null) {
			$this->t1 = $t1;
			$this->t2 = $t2;
		}
	}
	/* 
	 * Klasa wykorzystywana w 
	 * offsetGet w PairSettings
	 */
	interface QueryGenerator {
		public function genQueries();
	}
	class PairQuery implements QueryGenerator {
		public 	$ret_vals 		=	'';
		public 	$settings_tab 	=	'';
		public 	$joins  		=	null;
		public 	$opts 			=	'';

		public function __construct($settings_tab,
									$joins, 
									$ret_vals, 
									$opts = '') {
			$this->settings_tab = 	$settings_tab;
			$this->joins 		=	$joins;
			$this->ret_vals 	=	$ret_vals;
			$this->opts 		=	$opts;
		}
		public function genQueries() {
			return 	[ 
						"select {$this->ret_vals} 
							from {$this->settings_tab} l
					    inner join {$this->joins->t1}
					    inner join {$this->joins->t2} 
					    	{$this->opts}"
					];
		}
	}
	class PairSetting implements QueryGenerator {
		public 	$set_vals 		=	[ [] ];
		public 	$settings_tab 	= 	'';
		public 	$filter 		=	'';

		public function __construct($set_vals, $filter) {
			$this->set_vals 	=	$set_vals;
			$this->filter 		=	$filter;
		}
		public function genQueries() {
			$vals = '';
			foreach($this->set_vals as $val)
				$vals .= "(NULL,'".implode("','",$val)."'),";
			$vals = substr($vals, 0, -1);
			return 	[
						"delete from `{$this->settings_tab}` where {$this->filter}",
						"insert into `{$this->settings_tab}` VALUES{$vals}"
				   	];
		}
	}
	/* Manager ustawień */
	class PairSettings extends Module {
		private $cache = null;

		public function init($app) {
			$this->app_exts = [
				'pairSettings' 	=> 	function($self) {
					return $self->offsetGet('Core\PairSettings');
				}
			];
			parent::init($app);
		}
		public function offsetGet($pair_query) {
			return $this->app->sql()->queries($pair_query->genQueries(), true)[0];
		}
		public function offsetSet($table, $pair_setting) {
			$pair_setting->settings_tab = $table;
			$this->app->sql()->queries($pair_setting->genQueries(), true);
		}
		/* Szablony */
		public function lockedEnumVals($table, 
										Pair $cols, 
										Pair $join_tabs,
										$filter = '') {
			return $this->offsetGet(
					new PairQuery($table, 
						new Pair(
							"{$join_tabs->t1} o on l.{$cols->t1}=o.id",
							"{$join_tabs->t2} z on l.{$cols->t2}=z.id"
						),
						'z.*',
						"where {$filter}")
				);
		}
		/* Szablony predefiniowane */
		public function lockedZones($object_id) {
			return $this->lockedEnumVals(
							Messages::LOCKED_ZONES_TABLE,
							new Pair('object_id', 'zone_id'),
							new Pair(Messages::OBJECTS_TABLE, Messages::ZONES_TABLE),
							"o.id='{$object_id}'");
		}
		/* Dla użytkownika */
		public function lockedObjects() {
			$user_id = $this->app->userID();
			return $this->lockedEnumVals(
							Messages::LOCKED_USERS_TABLE,
							new Pair('user_id', 'object_id'),
							new Pair(Messages::USERS_TABLE, Messages::OBJECTS_TABLE),
							"o.id={$user_id}");
		}

		/* Ustawianie przyblokowanych enumów */
		public function setLockedObjects($vals, $user_id='') {
			/* [2, 1, 3] => [ [2, 17], [1, 17] .. ]*/
			$vals 			=	getSafeArray($vals);
			$user_id 		= 	getSafeParam($user_id, $this->app->userID());
			$parsed_vals 	= 	[ ];
			foreach($vals as $val)
				array_push($parsed_vals, [ $val, $user_id ]);
			$this->offsetSet(
							Messages::LOCKED_USERS_TABLE,
							new PairSetting($parsed_vals, "user_id={$user_id}")
						);
		}
		public function setLockedZones($vals, $id='') {
			/* [2, 1, 3] => [ [2, 17], [1, 17] .. ]*/
			$vals 			=	getSafeArray($vals);
			$user_id 		= 	getSafeParam($user_id, $this->app->userID());
			$parsed_vals 	= 	[ ];
			foreach($vals as $val)
				array_push($parsed_vals, [ $id, $val ]);
			$this->offsetSet(
							Messages::LOCKED_ZONES_TABLE,
							new PairSetting($parsed_vals, "object_id={$id}")
						);
		}
	}
	/* 
	 * Menedżer ustawień użytkownika, 
	 * wczytywanie wszystkiego jednym
	 * zapytaniem
	 */
	class Defaults {
		public 	$id 		 =	0;
		public 	$name 		 =	'';
		public 	$description =	'';
		public 	$type 		 =	0;
		public 	$flag 		 =	0;
		public 	$def_value 	 =	'';
		public 	$icon 		 =	'';
	}
	class UserSetting {
		public 	$id 			=	0;  // identyfikator ustawienia
		public 	$name 			=	'';
		public 	$flag 			=	0;
		public 	$type 			=	0;	// integer/bool/string/flag
		public 	$value 			=	'';

		public function __construct(
								$name  = '', 
								$flag  = 0, 
								$type  = 0, 
								$value = '') {
			$this->name  = $name;
			$this->flag  = $flag;
			$this->type  = $type;
			$this->value = $value;
		}
	}
	/*
	 * Ustawienie ma strukture:
	 * {$id} 	{$user_id} 	{$val}
	 */
	class SettingsManager extends Module {
		public 		$defs 			=	[]; 	// domyślna paczka ustawień, potrzebna do iteracji

		public function init($app) {
			$this->app_exts = [
				'settings' 	=> 	function($self) {
					return $self->offsetGet('Core\SettingsManager');
				}
			];
			parent::init($app);
			if($app->user())
				$this->loadSettings();
		}
		/*
		 * Odpowiedź na ajax
		 * @param 	$data 	$_POST
		 * @param 	$type 	Type
		 */
		public function response($data, $type) {
			$exit_code = 0;
			try {
				$this->offsetSet(
					$data['name'], 
					$data['value']);
			} catch(\Exception $e) {
				$exit_code = $e;
			}
			return [
				'exit_code' 	=> 	$exit_code,
			];
		}
		/* Wczytywanie wszystkich ustawień użytkownika */
		private function mapSettings($rows, &$map = null) {
			foreach($rows as $val)
				/* Przypisywanie ustawień nadanych przed użytkownika*/
				if(is_null($map)) {
					/* Jeśli tablica to niech przypisze */
					try {
						$arr = \json_decode(utf8_encode($val->value), true);
						if($arr)
							$val->value = $arr;
					} catch(\Exception $e) { }
					parent::offsetSet($val->name, $val);
				} else
				/* Dodawanie szablonów ustawień */
					$map[$val->name] = $val;
		}
		private function loadSettings() {
			$sql 		=	$this->app->sql();
			$logged_id 	=	$this->app->userID();

			/* Mapowanie ustawień domyślnych */
			$this->mapSettings(
				$sql->getRowsBy(
						new QueryInfo(Messages::SETTINGS_DESCRIPTION_TABLE), '\Core\Defaults'),
				$this->defs);
			/* Mapowanie ustawień użytkownika */
			$this->mapSettings(
				$sql->query("
						select  user_setting.id,
								description.name, 
								description.flag,
								description.type,
								user_setting.value
							from ".Messages::USERS_SETTINGS_TABLE." user_setting
							left join ".Messages::SETTINGS_DESCRIPTION_TABLE." description on user_setting.setting_id=description.id
						where
							user_setting.user_id={$logged_id}", 
					true, '\Core\UserSetting'));
		}
		/* 
		 * Zwracanie ustawienia 
		 * @param 	$i 	Nazwa ustawienia
		 */
		private function getDescriptionID($name) {
			return $this->app['Core\SQLwrapper']
						->queryObject("(select id from ".
									Messages::SETTINGS_DESCRIPTION_TABLE." 
									where name='{$name}' limit 1)")
						->id;
		}
		private function setDefaultSetting($i, $v = null) {
			/* Dodawanie nowego klucza */
			$sql 		 =  $this->app->sql();
			$logged_id	 = 	$this->app->userID();
			$def_setting =	$this->defs[$i];
			$def_val 	 = 	!isSetFlag($def_setting->flag, SettingFlag::BIT_FLAG)?
								$def_setting->def_value:
								0; 	// Flagi bitowe domyślnie nie ustawione
			$setting 	 = 	new UserSetting(
								$i,
								$def_setting->flag,
								$def_setting->type,
								is_null($v)?
									$def_val :
									$v);
			$sql->query(
					"insert into ".Messages::USERS_SETTINGS_TABLE."
						values(
							0, 
							{$logged_id}, 
							{$def_setting->id},
							'{$def_val}')"
				);
			$setting->id = $sql
							->getHandle()
							->insert_id;
			parent::offsetSet($i, $setting);
		}
		public function isSetParam($i) {
			return parent::offsetExists($i);
		}
		/*
		 * Przeciążanie operatorów tablicy
		 * @param 	$i 	Identyfikator ustawienia
		 * @param 	$v 	Wartość elementu
		 */
		public function offsetGet($i) {
			/* Ustawianie domyślnego parametru */
			if(!parent::offsetExists($i))
				$this->setDefaultSetting($i);

			$setting 	= parent::offsetGet($i);
			$func 		= SettingType::$TYPES[$setting->type];
			return empty($func) ? $setting->value : 
									$func($setting->value);
		}
		public function offsetSet($i, $v) {
			/* Jeśli jest sparsowanym ustawieniem to mapuj */
			if($v instanceof Setting)
				parent::offsetSet($i, $v);
			else {
				/* Jeśli jest wartością to zakoduj i do bazy */
				$logged_id = $this->app->userID();
				if(parent::offsetExists($i)) {
					/* Aktualizacja o ile istnieje */
					$setting 		= 	parent::offsetGet($i);
					$setting->value = 	$v;
					/* Przeczhowywanie tablic jako json */
					if(\is_array($v))
						$v = json_encode($v);
					/* Aktualizacja w bazie */
					$this->app
						->sql()
						->query(
							"replace into ".Messages::USERS_SETTINGS_TABLE."
								(`id`, `user_id`,`setting_id`,`value`) 
							values(	
								{$setting->id}, 
								{$logged_id}, 
								(select id from ".Messages::SETTINGS_DESCRIPTION_TABLE." where name='{$i}' limit 1), 
								'{$v}')");
				} else
					$this->setDefaultSetting($i, $v);		
			}
		}
	}
}
?>