<?php
namespace Core {
	use \BitFlag\Messages,
		\BitFlag\MessageFlag,
		\BitFlag\InboxFlag,
		\BitFlag\UserType,
		\BitFlag\SettingFlag;

	/* Skrzynka odbiorcza */
	class Inbox extends Module {
		public static 	$inbox_query;
		public static 	$message_flags;

		/*
		 * Cache zalogowanego usera
		 * @param 	$app 	Uchwyt aplikacji
		 */
		public function init($app) {
			parent::init($app);
		}
		public function isInbox($type) {
			return $type == InboxFlag::RENDER_RECEIVED;
		}
		private function filter($type, $sql_table, $setting_name) {
			$settings 	=	$this->app->settings();
			if($settings->isSetParam($setting_name) && 
						$settings[$setting_name]!=Messages::ROOT_FILTER_ID &&
						$this->isInbox($type)) {
				/* dodawanie nowych kryteriów */
				$setting 	=	$settings[$setting_name];
				return "{$sql_table}.id='{$setting}' and ";
			}
			return "";
		}
		public function getCriteria($type) {
			$criteria 	= 	'';
			$logged_id 	= 	$this->app->userID();
			$settings 	=	$this->app->settings();

			/* filtry todo: poprawa */
			$criteria .= $this->filter($type, 'object', 'object_filter').' '
							.$this->filter($type, 'zone', 'zone_filter');
			$criteria .= $this->isInbox($type) ?
						"queue.to_id   = {$logged_id}" :
						"queue.from_id = {$logged_id}";
			return $criteria;
		}
		public function genOrder() {
			return " order by queue.id desc ";
		}
		public function genLimit($type) {
			$settings 	= $this->app->settings();
			$limit 		= !$this->isInbox($type)?20:$settings['view_limit'];
			if($limit >= 100 || !is_numeric($limit)) {
				$limit 					= 100;
				$settings['view_limit'] = $limit;
			}
			return " limit {$limit}";
		}
		public function getListQuery($type) {
			return self::$inbox_query.
						' where '.
						$this->getCriteria($type).' '.
						$this->genOrder().' '.
						$this->genLimit($type);
		}
		/*
		 * Parsowanie dymka ustawień z 
		 * prawej górnej strony
		 */
		private function getFilterFlags() {
			$flag = 0;
			foreach($this->app->settings() as $key => $val)
				if(isSetFlag($val->flag, SettingFlag::BIT_FLAG))
					$flag |= $val->value;
			return $flag;
		}
		public function parseFilterMenu() {
			$settings  	=	$this->app->settings();
			$html 		= 	"<table class='table table-striped table-hover'>
								<thead>
									<th style='height: 39px'> Filtr </th>
									<th style='height: 39px'> Wartość </th>
								</thead>
								<tbody>";

			$pair_settings 		=	$this->app->pairSettings();
			foreach($settings->defs as $i => $val) {
				if(!isSetFlag($val->flag, SettingFlag::FILTER_SETTING))
					continue;

				$input 		= 	'';
				$setting 	=	$settings[$val->name];
				switch($val->type) {
					/* BOOL może być flagą bitową, nie ustawiona flaga to 0 */
					case 0:
						$input = "<input 
									class='pull-right {$val->name}' 
									type='checkbox' ".
									($setting?'checked':'').">";
					break;
					/* todo: INTEGER */
					case 1:
						$input 	= 	"<input 
										class='pull-right {$val->name}'
										type='number' 
										min='0' max='100'
										value='{$setting}' 
										style='max-width:100px; !important;'>";
					break;
					/* STRING */
					case 2:
						$object =  	$val->name == 'object_filter';
						$page 	= 	$this->app->page();
						$list 	=	($object?
											$page->listLockedObjects(
												$setting):
											$page->listLockedZones(
												$settings['object_filter'], 
												$setting));
						$input 	.= "<select class='{$val->name}' style='max-width:100px !important;'>".
										(strpos($list, '*')?"":"<option value='1'>*</option>").
										$list.
									"</select>";
					break;
				}
				$html .= "<tr><td>
								<i class='glyphicon glyphicon-{$val->icon}'></i> 
								{$val->description}
							</td>
							<td><label>{$input}</label></td></tr>";
			}
			return $html.'</tbody></table>';
		}
		/*
		 * Parsowanie dymka ustawień po lewej 
		 * górnej stronie strony
		 */
		public function parseToolsDropdown($dom_id, $type) {
			$exports_tab_id = $dom_id.'-exports-tab';
			return "<div class='btn-group'>
					  <button type='button' class='btn btn-danger dropdown-toggle glyphicon glyphicon-cog inbox-header-btn' data-toggle='dropdown'>
					  </button>
					  <ul class='dropdown-menu' role='menu'>
					    	<li><a href='javascript:;' class='inbox-remove'><i class='glyphicon glyphicon-trash'></i> Usuń ze skrzynki</a></li>".
					    ($this->app->admin()?"<li><a href='javascript:;' class='inbox-perm-remove'><i class='glyphicon glyphicon-remove'></i> Usuń z bazy</a></li>":"").
					    "<li class='divider' id='${exports_tab_id}'></li>
					    	<script>
					    		require([
					    			'jquery',
					    			'inbox/msg_exporter'
					    		], function($, _) {
					    			_.listTypes($('#${exports_tab_id}'));
					    		});
					    	</script>
					    <li class='divider'></li>
					    	<li><a href='javascript:;' class='inbox-content-refresh'><i class='glyphicon glyphicon-refresh'></i> Odśwież</a></li>
					    	<li><a href='javascript:;' class='inbox-content-print'><i class='glyphicon glyphicon-print'></i> Drukuj widoczne</a></li>
					    <li class='divider'></li>
					    	<li><a href='javascript:;' class='inbox-select-all'><i class='glyphicon glyphicon-ok-sign'></i> Zaznacz wszystko</a></li>
					    	<li><a href='javascript:;' class='inbox-deselect-all'><i class='glyphicon glyphicon-remove-sign'></i> Odznacz wszystko</a></li>".
					  		($this->isInbox($type)?"
					  			<li class='divider'></li>".
					  			($this->app->userType()!=UserType::WRITER?"<li><a href='javascript:;' class='inbox-mark-done'><i class='glyphicon glyphicon-ok'></i> Oznacz jako zrealizowane</a></li>":"").
					    		"<li><a href='javascript:;' class='inbox-mark-viewed'><i class='glyphicon glyphicon-eye-open'></i> Oznacz jako przeczytane</a></li>
					  			<li class='divider'></li>
					  			<li><a href='javascript:;' class='inbox-mark-starred'><i class='glyphicon glyphicon-star'></i> Oznacz przypięte</a></li>
					  			<li><a href='javascript:;' class='inbox-unmark-starred'><i class='glyphicon glyphicon-star-empty'></i> Odznacz przypięte</a></li>":"").
					  "</ul></div>";
		}
		/*
		 * Renderowanie całej skrzynki
		 * @param 	$user_id 	Identyfikator użytkownika w bazie
		 * @param 	$dom_id 	Identyfikator tabeli DOM
		 */
		public function render($type, $dom_id, $style = '') {
			$inbox 		=	$this->isInbox($type);
			$rows 		= 	$this
								->app['Core\SQLwrapper']
								->query($this->getListQuery($type), true);
			$defs 		=	\json_encode(
								array_values($this->app['Core\SettingsManager']->defs));
			echo "<div class='row'>
					<div class='col-xs-12' min-height='400px'>
						<table class='table table-hover' id='{$dom_id}' style='table-layout: fixed'>
						<thead><tr>
							<th width='42px'>".($this->parseToolsDropdown($dom_id, $type))."</th>
							<th width='20px'>ID </th>
							<th width='150px'>".($inbox?'Nadawca':'Odbiorca')."</th>
							<th class='col-xs-*'>Treść</th>
	           				<th style='width: 140px'>Obiekt</th>
	           				<th style='width: 140px'>Strefa</th>
	           				<th style='width: 95px'>Realizacja </th>
	           				<th style='width: 85px'>Wysłano </th>
	           				<th style='width: 100px'>".
	           					($inbox?
	           						"<div class='btn-group pull-right' style='padding-right: 0px'>
									  <button type='button' class='btn btn-default inbox-show-filter inbox-header-btn'>
									  	<span class='glyphicon glyphicon-filter'></span> Pokaż
									  </button>
									</div>":
									"Status").
	           				"</th>
	        			</tr></thead><tbody>".
	        				self::parseRows($type, $dom_id, $rows, $style).
	        			"</tbody></table></div>".
	        				($inbox?
		        				"<div class='col-xs-3 inbox-filter-table' style='display:none'>
			        				".$this->parseFilterMenu()."
			        			</div>":
		        			"").
        		 "</div>
        		 <script>
        		 require([
        		 		'inbox/inbox',
        		 		'inbox/utils'
        		 	], function(_, inbox_utils) {
        		 		inbox_utils.settings = {$defs};
        		 		new _.Inbox('{$dom_id}', {$type});
        		 		console.log('Dodany inbox!');
        		 	});
				</script>";
		}
		/**
		 * Pobieranie koloru w klasie dla wersetu
		 * @param  $style   ID css tabeli 
		 * @param  $row  	Werset
		 * @return Klasa koloru
		 */
		private function getRowColor($style, $row, $type) {
			return 
				$this->isInbox($type) && 
					$this->app->userID() == $row->to_id &&
					isSetFlag($row->flag, MessageFlag::MESSAGE_STARRED)?
			 	"{$style}-starred":
				(!isSetFlag($row->flag, MessageFlag::MESSAGE_VIEWED)?
					"{$style}-blink":
					"{$style}"
				);
		}
		/* 
		 * Parsowanie daty realizacji 
		 * @param 	$style 			Dodatkowa klasa
		 * @param 	$row 			Linia parsowana
		 */
		private function parseRealizationDate($style, $row, $type) {
			$row->realization_date = ($row->realization_date == '0000-00-00' ? 
										'' : 
										$row->realization_date);
			if($this->app->userType() == UserType::WRITER)
				return $row->realization_date;
			else
				return "
				    <div class='input-append date inbox-realization-date' data-date='{$row->realization_date}' data-date-format='yyyy-mm-dd'>
						<input class='span2 inbox-date-picker' size='8' value='{$row->realization_date}' readonly='readonly' style='cursor: pointer;".
							(!isSetFlag($row->flag, MessageFlag::MESSAGE_DONE)?'color:#333;':'').
						"'>
					    <span class='add-on'></span>
				    </div>";
		}
				/* 
		 * Parsowanie linii 
		 * @param 	$dom_id 			Identyfiaktor tabeli na której są wiadomości
		 * @param 	$rows 				Linie z bazy danych
		 * @param 	$style 				Dodatkowe klasy dla td
		 * @param 	$last_dom_ind 		Ostatni index elementu
		 */
		public function parseRows($type, $dom_id, $rows, $style = '', $last_dom_id = 0) {
			/* Podstawowe filtry */
			$count 		= 	count($rows);
			$html 		= 	'';
			$inbox 		=	$this->isInbox($type);
			$filter 	=	$this->getFilterFlags();
			$user_id 	=	$this->app->userID();

			/* Iteracja przez wszystkie wiersze */
			foreach($rows as $index => $row) {
				/* Sprawdzanie uprawnień */
				if(!$inbox && $row->from_id != $row->to_id)
					$row->flag <<= 9;
				if($row->from_id == $user_id) {
					$viewed = isSetFlag($row->flag, MessageFlag::MESSAGE_VIEWED);
					$row->flag >>= 9;
				}
				if(!$inbox)
					$row->flag &= ~MessageFlag::MESSAGE_REMOVED;
				$generated 	= isSetFlag($row->flag,MessageFlag::MESSAGE_GENERATED); // Czy wiadomość została wygenerowana?
				$deleted 	= isSetFlag($row->flag,MessageFlag::MESSAGE_REMOVED);
				if((!$inbox && $deleted) ||
						($inbox && $filter&$row->flag))
					continue;
			
				$index 				= $index+$last_dom_id+1;
				$collapse_id 		= "{$dom_id}-content-collapse-{$index}";
				$collapse_content 	= "{$collapse_id}-textarea";

				/* Tytuł */
				$title = (isset($row->last_message) ?
							'<b>Odpowiedź: </b>'.strip_tags($row->last_message) :
							strip_tags($row->content));
				$title_length = 44;
				if(isset($row->last_message))
					$title_length += 10;
				if(strlen($title) > $title_length)
					$title = substr($title, 0, $title_length).
								(strlen($title) > $title_length ? '...' : '');
				// if(!isset($row->last_message)) {
				// 	$row->content = substr($row->content, $title_length);
				// 	if(!empty($row->content))
				// 		$row->content = '...'.$row->content;
				// }
				unset($row->last_message);
				if(strip_tags($row->content)==$title)
					$row->content = '';
				/* Treść */
				$row->content = "
				          <span class='inbox-message-title' data-toggle='collapse' data-parent='#{$dom_id}-content-title-{$index}' href='#{$collapse_id}'>
				          	".($deleted?'<strong><i>usunięta: </i></strong>':'')."{$title}
				          </span>
					      <div id='{$collapse_id}' class='panel-collapse collapse'>".
					      		(!empty($row->content)?"
						      		<label>Treść:</label>":"").
						        	"<div class='inbox-message-content text-justify'>{$row->content}</div>";
				if(!$generated && $inbox)
					$row->content .= "
									<br>
						        	<label>Odpowiedź:</label>
						        	<span class='pull-left btn-group'>
							        	<button type='submit' title='Wyślij odpowiedź' class='btn btn-danger inbox-reply-button message-editor-button'>
							        		<div class='glyphicon glyphicon-share-alt'></div>
							        	</button>".
						        	($this->app->userType() == UserType::WRITER?
							        	"" :
							        	"<button type='submit' title='Zrealizuj' class='btn btn-primary inbox-done-button message-editor-button' style='margin-right: 2px'>
							        		<div class='glyphicon glyphicon-ok'></div>
							        	</button>");
				$row->content .= "</span></div>";
				/* Zaznaczanie usuwania */
				$html .= "<tr class='inbox-message-row ".$this->getRowColor($style, $row, $type)."'>
								<td class='inbox-select-column'>
									<input class='center-block inbox-select-field' type='checkbox' style='margin: 0 auto;'>
								</td>";

				/* Tworzenie kolumn za wykluczeniem flag */
				if($inbox)
					unset($row->to);
				else
					unset($row->from);
				$row->realization_date = '<b>'.$this->parseRealizationDate($style, $row, $type).'</b>';
				foreach($row as $key => $name) {
					if($key !== 'flag')
						$html .= "<td class='inbox-view-button inbox-{$key}-column' title='".($key=='content'?'':strip_tags($name))."'>{$name}</td>";
					if($key === 'date')
						break;
				}
				
				/* Tworzenie flag */
				$flag = '';
				foreach(self::$message_flags as $key => $val)
					if(isSetFlag($row->flag, $key))
						$flag .= "<div title='{$val[1]}' class='glyphicon {$val[0]}'></div> ";
				$html .= "<td class='inbox-flag-column'>{$flag}</td></tr>";
			}
			return $html;
		}
		/* 
		 * Odświeżanie skrzynki pocztowej odbywa
		 * się z poziomu JS, pobieranie ostatniego
		 * identyfikatora i zbieranie wszystkich z większym
		 */
		private function getUpdateCriteria($msg_id) {
			$logged_id 	=	$this->app->userID();
			return "queue.`id` = {$msg_id}
					and (`to_id` = {$logged_id} or 
						`from_id` = {$logged_id}) 
					and reports.id=queue.report_info_id";
		}
		public function response($data, $type) {
			$logged_id 	 = $this->app->userID();
			$exit_code   = InboxFlag::REFRESH_SUCCESS;
			$new_records = [ ];
			$sql 		 = $this->app->sql();
			try {
				switch($type) { 
					/* Pernamentne kasowanie z bazy danych */
					case 'permremove':
						if($this->app->admin())
							$this->app['Core\MessageManager']
									->removeMessage($data['msg_id'], true);
					break;

					/* Odświeżanie */
					case 'refresh':
						$last_id = $sql
								->query('select max(id) from '.Messages::QUERY_TABLE.' limit 1')
								->fetch_assoc()['max(id)'];
						$new_records = $sql
								->query( 
										self::$inbox_query.' where '.
											$this->getCriteria($data['type']).
											' and queue.id <> '.
											$data['last_id'].' and queue.id between '.
											($data['last_id']).' and '.$last_id.' order by queue.id desc '.
											($data['last_id']==-1?$this->genLimit($data['type']):''),
										true);
					break;
					
					/* Raports info współdzielone w odpowiedziach */
					case 'addflag':
					case 'delflag':
						$del     	= $type === 'delflag';
						$flag_table = $data['flag'] >= MessageFlag::MESSAGE_DONE ? 
											'reports' : 
											'queue';
						$from_id 	= $this->app->sql()
										->queryObject("select from_id from ".Messages::QUERY_TABLE." where id={$data['msg_id']}")
										->from_id;
						/* Przesunięcie bitowe 8 w lewo, flaga nadana przez osobę odczytującą */
						if($from_id == $this->app->userID())
							$data['flag'] <<= 9;
						exAssert(
								$data['flag'] != MessageFlag::MESSAGE_VIEWED ||
									(isset($data['type']) && $this->isInbox($data['type'])), 
								'Flag error!');
						$sql->query("update ".Messages::QUERY_TABLE." queue, 
											".Messages::REPORTS_TABLE." reports
								set {$flag_table}.flag=".
									($del?"${flag_table}.flag&~{$data['flag']}":"${flag_table}.flag|{$data['flag']}").
								" where 
									".$this->getUpdateCriteria($data['msg_id']));
					break;

					/* Nadawanie daty realizacji */
					case 'realizedate':
						$data['realize_date'] = date('Y-m-d', strtotime(str_replace('-', '/', $data['realize_date'])));
						$sql->query("update ".Messages::QUERY_TABLE." queue, 
											".Messages::REPORTS_TABLE." reports
								set reports.realization_date='${data['realize_date']}'
								where 
									".$this->getUpdateCriteria($data['msg_id']));
					break;
				}
			} catch(\Exception $e) {
				$exit_code = InboxFlag::REFRESH_ERROR;
			}
			return [
				'exit_code' 	=> 	$exit_code,
				'new_records' 	=> 	$type === 'refresh' ? 
										self::parseRows($data['type'],
														$data['dom_id'],
														$new_records, 
														$data['style'], 
														intval($data['last_dom_id'])) :
										[ ]
			];
		}
	}
	Inbox::$message_flags = [
		MessageFlag::MESSAGE_REPLY 				=> 	[ 'glyphicon-share-alt','Odpowiedź'					],
		MessageFlag::MESSAGE_VIEWED 			=> 	[ 'glyphicon-eye-open', 'Odczytana przez odbiorcę' 	],
		MessageFlag::MESSAGE_DONE				=> 	[ 'glyphicon-ok', 		'Zrealizowana' 				],
		/* true - wyświetlanie tylko u nadawcy */
		MessageFlag::MESSAGE_STARRED			=> 	[ 'glyphicon-star', 	'Przypięta' 				]
	];
	/* Wszystkie dodatkowe pola po date nie są wyswietlane */
	Inbox::$inbox_query = '
			select 	queue.id,
					CONCAT(user_from.name, " ",user_from.surname) `from`,
					CONCAT(user_to.name, " ", user_to.surname)    `to`,
					msgs.content,
			        msg_reply.content			`last_message`,
			        object.name					`object`,
			        zone.name					`zone`,
			        reports.realization_date,
			        reports.flag|queue.flag 	`flag`,
			        DATE_FORMAT(msgs.date, "%d/%m %H:%i") 	`date`,
			        user_from.id `from_id`,
					user_to.id   `to_id`

			from messages_queue queue
					left join '.Messages::MESSAGES_TABLE.' msgs 	    on msgs.id = queue.content_id
					left join '.Messages::MESSAGES_TABLE.' msg_reply 	on msg_reply.id = queue.reply_id
					left join '.Messages::USERS_TABLE.'    user_from  	on user_from.id = queue.from_id
					left join '.Messages::USERS_TABLE.'    user_to    	on user_to.id = queue.to_id
			        
			        left join '.Messages::REPORTS_TABLE.'  reports 	    on reports.id = queue.report_info_id
					left join '.Messages::OBJECTS_TABLE.'  object   	on object.id = reports.object
					left join '.Messages::ZONES_TABLE.'    zone       	on zone.id = reports.zone';
}
?>