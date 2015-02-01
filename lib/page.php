<?php
namespace Core {
	use \BitFlag\Messages,
		\BitFlag\PageFlag,
		\BitFlag\SettingEnum;
	
	/* Podczas aktualizacji systemu trzeba wczytać od nowa jsona */
	class EnumTemplate {
		/*
		 * Typ enumeratora
		 * Enumerator w postaci checkboxów dla wielu 
		 * elementów w bazach
		 */
		public 	$type;
		/*
		 * Tabela, z której mają być brane 
		 * nazwy enumeratorów
		 */
		public 	$table;
		/* 
		 * 	W JS zwracany jest cały row, nie wiadomo jak go porównywać, jako id czy name
		 *  type w tabeli users jest przechowywana jako id do types
		 *  filtry itp. są przechowywane jako tekst temu trzeba porównywać jako tekst 
		 * 	wprowadzone do value inputów
		 */
		public 	$entry_col;
		/*
		 * Aktualizacja wartości enumeratora, ustawienia
		 * itp., na podstawie jakiej tabeli ma się aktualizować
		 * np. user_id czy tylko same id
		 */
		public 	$callback;

		public function __construct(
					$type, 
					$table,
					$entry_col,
					$callback  = null) {
			$this->type 				= 	$type;
			$this->table 				=	$table;
			$this->entry_col 			=	$entry_col;
			$this->callback 			= 	$callback;
 		}
	}
	class SettingsJSON {
		public $rows 		=	'';
		public 	$template 	=	'';

		public function __construct($rows = '', 
									$template = '') {
			$this->rows 	= $rows;
			$this->template = $template;
		}
	}

	/* Tworzenie tablicy z normalnego elementu */
	function getSafeArray(&$array) {
		return is_array($array) ? $array : [ $array ];
	}
	function getSafeParam(&$val, $def) {
		return isset($val)?$val:$def;
	}
	function jsonBool($val) {
		return $val != 'false';
	}
	function renderHeader($text, $size=5) {
		$len 	= 	strlen($text);
		return "<h{$size} style='font-weight: bold'>{$text}</h{$size}>";
	}
	/* Szablon dla ustawień tabeli */
	abstract class SettingTemplate {
		protected 	$template;
		protected 	$table;

		public function __construct($table, $template=null) {
			$this->table 	=	$table;
			$this->template = 	$template;
		}
		/* Wszystkie funkcje zwracają kwerendę */
		abstract public function removeFromTable($data);
		abstract public function insertToTable($data);
		abstract public function render(&$settings_page, &$settings_json = null);
	}
	/* Szablon = translacja nazwy kolumny na deskrypcje w inputcie */
	class UserSettings extends SettingTemplate {
		public static $users_list_query;

		public function __construct() {
			parent::__construct(Messages::USERS_TABLE, [
				'login' 		=> 	['Login',				''],
				'name'			=> 	['Imię',				''],
				'surname'		=> 	['Nazwisko',			''],
				'password'		=> 	['Hasło',				'type="password"'],
				'job' 			=>	['Stanowisko',			''],
				'type' 			=> 	['Typ użytkownika',		''],
				'object_lock' 	=> 	['Obiekt przypisany',	''],
				'zone_lock'		=> 	['Strefa przypisana',	'']
			]);
		}

		public function removeFromTable($data) {
			return [
				"delete from ".Messages::LOCKED_USERS_TABLE." where user_id={$data['id']}",
				"delete from ".Messages::QUERY_TABLE." where from_id={$data['id']} or to_id={$data['id']}",
				"delete from ".Messages::USERS_SETTINGS_TABLE." where user_id={$data['id']}",
				"delete from ".Messages::USERS_TABLE." where id={$data['id']}"
			];
		}
		public function insertToTable($data) {
			return [
				"insert into {$data['table']}(`type`, `login`) values(1, 'new')"
			];
		}
		public function render(&$settings_page, &$settings_json = null) {
			return $settings_page->renderTableRow(Messages::USERS_TABLE, 
									self::$users_list_query, 
									$this->template, 
									function($id, $index, $user) use($settings_page) {
				return "<p class='text-muted text-center' style='font-weight:bold'>{$index}. {$user->type_title}</p>
				      	<img src='img/avatar.png' alt='...' width=128px height=128px>
				      	<div class='caption'>".
				      		renderHeader("{$user->name}<br>{$user->surname}", 4).
				        	"Stanowisko:
				        	<p style='white-space: nowrap;overflow:hidden;'><strong>{$user->job}</strong></p>
				        	<input type='hidden' name='ID' placeholder='{$user->id}'></input>".
				        	$settings_page->parseRowToolbar($id).
				      	"</div>";
			}, 2, $settings_json);
		}
	}
	UserSettings::$users_list_query = "
			select
				user.id,
			    user.login,
			    user.name,
			    user.surname,
			    user.password,
			    user.job,
			    user_type.id 		`type`,
			    user_type.name 		`type_title`,
			    CONCAT('[',IFNULL((SELECT GROUP_CONCAT(object_id SEPARATOR ',') FROM ".Messages::LOCKED_USERS_TABLE." WHERE user_id=user.id),''),']') 	`object_lock`
			from `".Messages::USERS_TABLE."` user 
			    left join ".Messages::USERS_TYPES_TABLE." user_type on user_type.id=user.type
			order by user.id";
	
	class ObjectSettings extends SettingTemplate {
		public function __construct() {
			parent::__construct(Messages::OBJECTS_TABLE, [
				'name' 		=> 	['Nazwa',	''],
				'zone_id' 	=> 	['Strefa',	'']
			]);
		}
		public function removeFromTable($data) {
			$object_id = $data['id'];
			return [
				"SET foreign_key_checks=0",
				/* Kasowanie blokad */
				"delete z,u from ".Messages::LOCKED_ZONES_TABLE." as z
						left join ".Messages::LOCKED_USERS_TABLE." u on u.object_id=z.object_id
					where z.object_id={$object_id}",
				/* Kasowanie wiadomości */
				"delete r,q,m from ".Messages::REPORTS_TABLE." as r
						left join ".Messages::QUERY_TABLE." q on r.id=q.report_info_id
					    left join ".Messages::MESSAGES_TABLE." m on m.id=q.content_id
					where r.object={$object_id}",
				"SET foreign_key_checks=1",
				"delete from {$data['table']} where id={$object_id}",
			];
		}
		public function insertToTable($data) {
			return [
				"insert into {$data['table']}(`name`) values('".getSafeParam($data['name'], 'Nowy enum')."')",
				"insert into ".Messages::LOCKED_ZONES_TABLE."(object_id, zone_id) values(
						(SELECT MAX(id) `id` from ".Messages::OBJECTS_TABLE."),
						(SELECT MAX(id) `id` from ".Messages::ZONES_TABLE."))"
			];
		}
		public function render(&$settings_page, &$settings_json = null) {
			return $settings_page->renderTableRow($this->table, 
									"select
										id,
										object.name `name`,
										object.id 	`id`,
										CONCAT('[',IFNULL((SELECT GROUP_CONCAT(zone_id SEPARATOR ',') FROM ".Messages::LOCKED_ZONES_TABLE." WHERE object_id=object.id),''),']') 	`zone_id`
									from `".($this->table)."` object order by id", 
									$this->template, 
									function($id, $index, $val) use($settings_page) {
				return "<p class='text-muted text-center' style='font-weight:bold'>{$index}. Element</p>
				      	<div class='caption'>
				        	<span>Obiekt:</span> ".
				      		renderHeader("{$val->name}", 5).
				        	"<input type='hidden' name='ID' placeholder='{$val->id}'></input>".
				        		($val->name=='*'?
					        		"<em>Wartość stała</em>":$settings_page->parseRowToolbar($id))."
				      	</div>";
			}, 2, $settings_json);
		}
	}
	class EnumSettings extends SettingTemplate {
		public function __construct($table) {
			parent::__construct($table, [
				'name' 	=> 	['Nazwa',	''],
			]);
		}
		public function removeFromTable($data) {
			return [
				"delete from {$data['table']} where id={$data['id']}"
			];
		}
		public function insertToTable($data) {
			return [
				"insert into {$data['table']}(`name`) values('".getSafeParam($data['name'], 'Nowy enum')."')"
			];
		}
		public function render(&$settings_page, &$settings_json = null) {
			return $settings_page->renderTableRow($this->table, 
									"select id, name from {$this->table} order by id", 
									$this->template, 
									function($id, $index, $val) use($settings_page) {
				return "<p class='text-muted text-center' style='font-weight:bold'>{$index}. Element</p>
				      	<div class='caption'>
				        	<span>Wartość:</span> ".
				      		renderHeader("{$val->name}", 5).
				        	"<input type='hidden' name='ID' placeholder='{$val->id}'></input>".
				        		($val->name=='*'?
				        			"<em>Wartość stała</em>":$settings_page->parseRowToolbar($id))."
				      	</div>";
			}, 2, $settings_json);
		}
	}
	class SettingsPage extends Module {
		public static $enum_fields; //	pola, które muszą być listami
		
		public $sub_pages = [];
		public function init($app) {
			$this->app_exts = [
				'settingsPage' 		=>  function($self) {
					return $self->offsetGet('Core\SettingsPage');
				}
			];
			$this->sub_pages 	=	[
				Messages::USERS_TABLE 	=> 	new UserSettings,
				Messages::OBJECTS_TABLE => 	new ObjectSettings,
				Messages::ZONES_TABLE 	=> 	new EnumSettings(Messages::ZONES_TABLE)
			];
			parent::init($app);
		}
		/* Odpowiedzi serwera */
		public function response($data, $type) {
			$exit_code 	= 	0;
			if(!$this->app->admin())
				return;
			try {
				switch($type) {
					/** Kasowanie elementu z tabeli */
					case 'delete':
						$this->removeFromTable($data);
					break;
					/** Aktualizacja elementu w jakiejś tabeli */
					case 'update':
						$this->updateSettingsTable($data);
					break;
					/** Dodawanie elementu do jakiejś tabeli */
					case 'insert':
						$this->insertToTable($data);
					break;
				}
			} catch(\Exception $e) {
				$exit_code = $e;
			}
			/* Zwracanie zaktualizowanego html'a */
			if(isset($data['fetch_table']) && 
					!jsonBool($data['fetch_table']))
				return [
					'exit_code' 	=> 	$exit_code
				];
			$json_data 	= 	new SettingsJSON;
			$new_html 	=	$this->render(
				getSafeParam($data['fetch_table'], $data['table']), 
				$json_data);
			$new_html 	= substr($new_html, 0, strpos($new_html, '<script>'));
			return [
				'exit_code' 	=> 	$exit_code,
				'new_html' 		=> 	$new_html,
				'json_data'		=> 	$json_data
			];
		}
		/*
		 * Aktualizacja tabeli ustawień, wywoływana spod JSa
		 * @param 	$form 	$_POST
		 */
		private function updateSettingsTable($data) {
			/* Aktualizacja ustawień */
			$form = $data['data'];
			$sql  =	$this->app->sql();

			/* Parsowanie na jsona tablicy jak się natrafi */
			foreach($form as $key => &$val)
				if(is_array($val))
					$val= json_encode($val);

			if(isset($form['password']))
				$form['password'] = SQLwrapper::encryptPassword($form['password']);
			/* Enumaeratory zmieniają wartości w różnych tabelach */
			foreach($form as $key => &$val)
				if(array_key_exists($key, self::$enum_fields)) {
					$enum = self::$enum_fields[$key];
					if(is_null($enum->callback))
						continue;
					/* Callback do obsługi enuma */
					$decoded = json_decode($val);
					call_user_func_array($enum->callback, 
									array($this->app, 
										$key, 
										$decoded?$decoded:$val, 
										$data));
					unset($form[$key]);
				}
			/* Tworzenie głównego zapytania */
			if(!empty($form)) 
				$sql->query(
					$sql->parseUpdateQuery(
						$data['table'],
						$form,
						[ 'id' 	=> 	$data['id'] ]
					));
		}
		/* 
		 * Kasowanie użytkownika z bazy
		 * @param 	$data 	$_POST
		 */
		public function removeFromTable($data) {
			$this->app
				->sql()
				->queries($this->sub_pages[$data['table']]->removeFromTable($data));
		}
		/* 
		 * Dodawanie użytkownika z bazy
		 * @param 	$data 	$_POST
		 */
		public function insertToTable($data) {
			$this->app
				->sql()
				->queries($this->sub_pages[$data['table']]->insertToTable($data));
		}
		/* 
		 * Renderowanie wersetu tabeli jako
		 * panel ustawień
		 */
        public function renderTableRow($id, $query, $template, $element_callback, $vsize=2, &$settings_json = null) {
        	if(!$this->app->admin())
        		return;
        	/* Kwerenda z bazy */
        	$template['id'] = 	['ID', ''];
        	$settings_id 	=	'settings-'.$id;
        	$rows 			= 	$this->app->sql()->query($query, true);
        	$html 			=	($settings_json?"<div class='alert alert-warning' role='alert'> 
    								<p class='text-center'>
    									<span class='glyphicon glyphicon-warning-sign'></span>
    									<strong>Odśwież stronę zmiany były widoczne w panelu wiadomości</strong>
    								</p>
    							</div>":"").
        					"<div id='{$settings_id}'>
				        		<button type='button' class='btn btn-success {$id}-add' data-options='{$id}'>
									<span class='glyphicon glyphicon-plus'></span> Dodaj
								</button>";
    		foreach($rows as $index => &$val) {
    			/* Odrzucanie zbędnych kolumn */
        		$html .= 
        				"<div class='thumbnail col-xs-{$vsize} {$id}-row' style='margin-right: 5px; margin-bottom: 5px;'>".
        				$element_callback($id, $index, $val).
        				"</div>";
        		/* Przerabianie tabeli na JSONA, pakowanie wszystkich enumów */
        		foreach(self::$enum_fields as $key => $enum_table) {
        			/* Testowanie czy w row są elementy z enuma */
        			if(!isset($val->$key))
        				continue;
        			/*  GENEROWANIE CHECKBOXÓW ENUMERATORÓW */
        			$encoded 	= \json_decode(utf8_encode($val->$key), true);
        			$val->$key 	= [
        				'table' 		=> 	$enum_table->table,
        				'value' 		=>	$encoded?$encoded:($val->$key),
        				'data' 			=> 	$this->app->page()->getEnumArray($enum_table->table),
        				'type' 			=> 	$enum_table->type,
        				'entry_col' 	=> 	$enum_table->entry_col
        			];
        		}
    		}
    		/* 
    		 * JSON potrzebny podczas aktualizacji ustawień, 
    		 * nie trzeba od nowa wczytywać strony 
    		 */
        	$settings_json = new SettingsJSON($rows, $template);
			return $html."</div>
						<script>
							require([
								'jquery',
								'admin/sql_editor'
							], function($, sql_editor) {
								new sql_editor.SQLEditor(
									'{$id}',
									'settings-{$id}',
									".json_encode($settings_json->rows).",
									".json_encode($settings_json->template)
								.");
							});
						</script>";
        }
        public function parseRowToolbar($id) {
        	return "<button type='button' class='btn btn-default {$id}-edit'><span class='glyphicon glyphicon-edit'></span></button>
					<button type='button' class='btn btn-danger {$id}-delete'><span class='glyphicon glyphicon-trash'></span></button>";
        }
        public function render($table, &$settings_json = null) {
        	return $this->sub_pages[$table]
        				->render($this, $settings_json);
        }
	}
	/* Główna klasa strony */
	class Page extends Module {
		public function init($app) {
			$this->app_exts = [
				'page' 		=>  function($self) {
					return $self->offsetGet('Core\Page');
				}
			];
			parent::init($app);
		}
		public function response($data, $type) {
			$exit_code 	= 	0;
			try {
				switch($type) {
					/** Zbieranie stref dla określonego obiektu */
					case 'fetchzones':
						return [
							'new_html' 	=> 	$this->listLockedZones($data['object'])
						];
					break;
					/** Zmiana hasła usera */
					case 'changepassword':
						$exit_code = 
							$this->app->sql()->changePassword($data['old_pass'], $data['new_pass'])?
								PageFlag::PASSWORD_CHANGED:
								PageFlag::OLD_PASSWORD_INCORRECT;
					break;
				}
			} catch(\Exception $e) {
				$exit_code = $e;
			}
			return [
				'exit_code' 	=> 	$exit_code
			];
		}
		/* Jeśli zalogowany to display: default, jeśli nie to none */
		public function loginRequired($on='default', $off='none') {
			$logged = $this->app->user();
			echo 'style="display:'.
					($logged?$on:$off).
					'"';
		}
		public function anonymRequired() {
			$this->loginRequired('none', 'default');
		}
		/* Listuje elementy do option */
		public function getEnumArray($table) {
			if(Cache::exists('Page','cache_enums'))
				return Cache::get('Page','cache_enums');
			return $this->app
						->sql()
						->getRowsBy(new \Core\QueryInfo($table, [], ' order by id'));
		}
		/*
		 * Wyświetlanie listy z sql
		 * @param 	$array 	Tablica
		 * @param 	$table 	Tabela
		 */
		public function listSqlEnum($array, $table='', $selected='') {
			if((empty($array) || $array[0]->name == '*') && !empty($table))
				$array = $this->getEnumArray($table);
			$html 	= 	'';
			$array 	=	getSafeArray($array);
			foreach($array as $val)
            	$html .= "<option value='{$val->id}'".
            				($selected===$val->id?"selected":"").">".
            				(isset($val->name)?$val->name:$val).
            			"</option>";
            return $html;
        }
        public function listLockedObjects($selected='') {
        	return $this->listSqlEnum(
        			$this->app->pairSettings()->lockedObjects(),
        			Messages::OBJECTS_TABLE,
        			$selected
        		);
        }
        public function listLockedZones($object_id, $selected='') {
        	return $this->listSqlEnum(
        			$this->app->pairSettings()->lockedZones($object_id),
        			Messages::ZONES_TABLE,
        			$selected
        		);
        }
        public function listUsers($group_id) {
            // SELECT `name`,`surname`,`job`,`login` FROM `users` u inner join `users_groups` g on (g.user_id=u.id and g.group_id=1)
            $html  = '';
            $logged     =   $this->app->user();
            $users      =   $this->app->sql()->getRowsBy(
                                  new \Core\QueryInfo(
                                    \BitFlag\Messages::USERS_TABLE, 
                                    [], 
                                    ' order by type desc'
                                  ));
            $user_tooltips = [
                7 => 'Administracja programu:',
                3 => 'Moderatorzy:',
                1 => 'Zgłaszający:'
              ];
            $last_user_type = 0;
            foreach($users as $user) {
                if($user->type == \BitFlag\UserType::WRITER && 
                        $logged->type == \BitFlag\UserType::WRITER)
                  continue;
                if($user->type != $last_user_type) 
                  $html .= '</optgroup><optgroup label="'.$user_tooltips[$user->type].'">';

                $text = "{$user->name} {$user->surname} - {$user->job}";
                $html .= "<option value='{$user->login}' title='{$text}'>{$text}</option>";
                $last_user_type = $user->type;
            }
            return $html.'</optgroup>';
        }
	}
	/* SZABLONY */
	SettingsPage::$enum_fields = [
		/* SINGLE ENUM */
		'type' 			=>	new EnumTemplate(
									SettingEnum::SINGLE_ENUM, 
									Messages::USERS_TYPES_TABLE, //	tabela
									'id'),
		'object_id' 	=>	new EnumTemplate(
									SettingEnum::SINGLE_ENUM, 
									Messages::OBJECTS_TABLE, 	//	tabela
									'id'),
		/* MULTI ENUM */
		'zone_id' 		=>	new EnumTemplate(
									SettingEnum::MULTI_ENUM,  
									Messages::ZONES_TABLE,
									'id',
			function($app, $key, $val, $data) {
				$app->pairSettings()->setLockedZones(
													$data['data']['zone_id'], 
													$data['id']);
			}),
		'object_lock' 	=>	new EnumTemplate(
									SettingEnum::MULTI_ENUM,  
									Messages::OBJECTS_TABLE,
									'id',
			function($app, $key, $val, $data) {
				$app->pairSettings()->setLockedObjects($val, $data['id']);
			})
	];
}
?>