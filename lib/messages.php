<?php
namespace Core {
	include_once	'database.php';
	use \BitFlag\Messages,
		\BitFlag\MessageFlag;

	/**
	 * Usuwanie dodatkowych znaków
	 */
	function stripTags($str) {
		return trim(strip_tags($str, '<br><b><i><u><ul><li><img><div>'));
	}
	/*
	 * Klasa wysyłająca informacje/wiadomości
	 * bazy danych
	 */
	class Sender {
		private $send_table 	=	'';

		public function __construct($send_table = '') {
			$this->send_table = $send_table;
		}
		public function send($sql) {
			if(isset($sql))
				$sql->insertInto(
					$this->send_table,  
					$this);
		}
	}
	/*
	 * Informacje nt. raportu, jak się odsyła
	 * to identyfikator w bazie się kopiuje
	 */
	class ReportInfo extends Sender {
		public 	$id 	=	0;
		public 	$object = 	0;
		public 	$zone 	=	0;
		public 	$flag 	=	0;
		public 	$realization_date 	=	'0000-00-00';

		public function __construct(
					$object, 
					$zone,
					$flag = 0) {
			parent::__construct(Messages::REPORTS_TABLE);

			$this->object 	= $object;
			$this->zone 	= $zone;
			$this->flag 	= $flag;
		}
	}
	/* 
	 * Nagłówek wiadomości, jedna wiadomość
	 * może zostać wysłana do kulku użytkowników
	 */
	class MessageHeader extends Sender {
		public 	$id 		=	0; 	// identyfikator wiadomości
		public 	$from_id 	=	0;	// identyfikator usera
		public 	$to_d 		=	0; 	// -1 to globalnie
		public 	$flag 		=	0;
		public 	$report_info_id 	=	0;  // 	identyfikator raportu
		public 	$content_id			=	0;	//	identyfikator contentu

		public function __construct(
					$from_id = 0, 
					$to_id 	 = 0,
					$flag 	 = 0) {
			parent::__construct(Messages::QUERY_TABLE);

			$this->from_id 		=	$from_id;
			$this->to_id 		=	$to_id;
			$this->flag 		=	$flag;
		}
		public function send($sql) {
			$sql->query(
				"insert ignore into ".Messages::QUERY_TABLE."(`from_id`,`to_id`,`report_info_id`,`content_id`) 
					values(
						{$this->from_id}, 
						{$this->to_id}, 
						(select max(`id`) from ".Messages::REPORTS_TABLE."), 
						(select max(`id`) from ".Messages::MESSAGES_TABLE.")
					)"
			);
		}
	}
	/* Wiadomość */
	class MessageContent extends Sender {
		public 	$id 		=	0;
		public 	$content 	=	'';
		public 	$date 		=	'';

		public function __construct($content) {
			parent::__construct(Messages::MESSAGES_TABLE);

			$this->content 			= 	$content;
			$this->date 			=	date("Y-m-d H:i:s");
		}
		public function send($sql) {
			$this->content = stripTags($this->content);
			parent::send($sql);
		}
	}
	class Message extends Sender {
		public 	$header 		=	null;
		public 	$content 		=	null;
		public 	$report_info	=	null;

		public function __construct($header, $content, $report_info) {
			$this->header 		= 	$header;
			$this->content 		=	$content;
			$this->report_info 	=	$report_info;
		}
		public function send($sql) {
			$this->content->send($sql);
			$this->report_info->send($sql);
			$this->header->send($sql);
		}
	}
	/* Menedżer wiadomości, zajmuje się listowaniem i wysyłaniem */
	class MessageManager extends Module {
		/*
		 * Wiadomość wysłana przez AJAX do modułu
		 * @param 	$data 	Dane już sparsowane JSON
		 */
		public function send(Message $message) {
			$message->send($this->app['Core\SQLwrapper']);
		}
		/* 
		 * Odpowiedź na wiadomość
		 * @param 	$content 	Zawartość wiadomości
		 * @param 	$msg_id 	Identyfikator odpowiadanej 
		 *						wiadomości w tabeli QUERY_TABLE
		 */
		public function messageResponse($content, $msg_id, $flag=0) {
			$sql 		= $this->app->sql();
			$reply_id 	= $sql->queryObject("select content_id from ".
											Messages::QUERY_TABLE." where id={$msg_id}")
								->content_id;
			/* Wysyłanie */
			(new MessageContent($content))->send($sql);
			$sql->queries([
					/* Duplikowanie wiadomości */
					"insert into  ".Messages::QUERY_TABLE."(`from_id`,`to_id`,`report_info_id`,`content_id`,`reply_id`) 
						(select `from_id`,`to_id`,`report_info_id`,`content_id`,`reply_id` from ".Messages::QUERY_TABLE." where `id`={$msg_id})",
					
					/* Podmiana flag i nadawcy i odbiorcy */
					"update ".Messages::QUERY_TABLE." set 
						`from_id`=(@temp:=`from_id`),
						`from_id`=`to_id`,
						`to_id`=@temp,
						`flag`='".(MessageFlag::MESSAGE_REPLY|$flag)."',
						`content_id`=(SELECT MAX(id) FROM ".Messages::MESSAGES_TABLE.") 
					where `id`=LAST_INSERT_ID()",

					/* Aktualizacja odpowiedzi o ile starsza nie była odpowiedzią */
					"update ".Messages::QUERY_TABLE." set 
						`reply_id`={$reply_id}
					where `id`=LAST_INSERT_ID() and `reply_id`='-1'"
				]);
		}
		/*
		 * Kasowanie wiadomości z całego systemu!!!
		 * @param 	$msg_id 	Identyfikator wiadomości
		 */
		public function removeMessage($msg_id) {
			$sql 		= 	$this->app->sql();
			$row 		= 	$sql->queryObject("select 
									queue.id, 
								    queue.report_info_id, 
								    queue.flag, 
								    count(queue2.report_info_id) reports_count
								from ".Messages::QUERY_TABLE." queue
							     	left join ".Messages::QUERY_TABLE." queue2 on 
							        	(queue2.report_info_id=queue.report_info_id and queue2.id<>queue.id)
								where queue.id={$msg_id}");
			$query = "delete msgs, queue ".
					(!$row->reports_count?",reports  ":"").
					"  from ".Messages::MESSAGES_TABLE." msgs ".
					" inner join ".Messages::QUERY_TABLE." queue ".
					(!$row->reports_count?" inner join ".Messages::REPORTS_TABLE." reports " : "").
					" 	where 
							msgs.id=queue.content_id and ".
							(!$row->reports_count?"reports.id=queue.report_info_id and ":"").
							"queue.id={$msg_id} and
							(queue.from_id={$_SESSION['id']} or queue.to_id={$_SESSION['id']})";
			$sql->query($query);
		}
		/* 
		 * Odpowiedź na forma z okna html
		 * @param 	$data 	$_POST
		 * @param 	$type 	Typ akcji z formularza/pusty
		 */
		public function response($data, $type) {
			$exit_code = MessageFlag::SENT_SUCCES;
			try {
				$sql 		= 	$this->app->sql();
				$content 	=	$data['content'];
				if(strlen($content) >= Config::MAX_MSG_LENGTH)
					throw new \Exception;
				if($type === 'response')
					$this->messageResponse(
							$content, 
							$data['reply_id'],
							isset($data['flag'])?$data['flag']:0
						);
				else {
					$receiver	=	$sql->getUserBy([
						'login' => $data['receiver']
					]);
					foreach($data as $key => $val)
						if(empty($val))
							throw new \Exception($key.' : '.$val);

					$this->send(new Message(
							new MessageHeader(
								$_SESSION['id'],
								$receiver->id
							),
							new MessageContent(
								$content
							),
							new ReportInfo(
								$data['object'],
								$data['zone']
							)
						));
				}
			} catch(\Exception $e) {
				$exit_code = MessageFlag::SENT_ERROR;
			}
			return [
				'exit_code' => $exit_code
			];
		}
	}
}
?>