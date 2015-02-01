<?php 
namespace Core {
	class Config {
		public $database =   [
			'host'		=>	'localhost',
			'user'		=>	'root',
			'password'	=>	'dell',
			'database'	=>	'reports'
		];
        const SALT              =   's124&cbas@$^';
        const MAX_MSG_LENGTH    =   512;
	}
}
namespace BitFlag {
	/* Wiadomości */
	interface MessageFlag {
		const SENT_SUCCES 		=	0;
		const SENT_ERROR 		=	1;

		const MESSAGE_VIEWED				=	2;
		const MESSAGE_REMOVED			 	=	4;
		const MESSAGE_STARRED 				=	8;
		const MESSAGE_REPLY 				=	16;

		const MESSAGE_DONE 					=	32;
		const MESSAGE_GENERATED 			=	64;
	}
	interface Messages {
        const RELEASE_NOTES         =   'release_notes';
        
		const USERS_TABLE 			= 	'users';
		const USERS_TYPES_TABLE 	= 	'users_types';

        const GROUPED_USERS         =   'users_groups';
        const GROUPS_TABLE          =   'groups';

		const MESSAGES_TABLE 	= 	'messages';
		const QUERY_TABLE 		=	'messages_queue';

		const OBJECTS_TABLE 	=	'objects';
		const ZONES_TABLE 		=	'zones';
		const REPORTS_TABLE 	=	'reports_info';

		const SETTINGS_DESCRIPTION_TABLE 	=	'settings';
		const USERS_SETTINGS_TABLE 			=	'users_settings';

		const LOCKED_ZONES_TABLE 	=	'locked_zones';
		const LOCKED_USERS_TABLE 	=	'locked_users';
		const ROOT_FILTER_ID 		=	1; // id gwiazdki
	}
	/* Użykwonicy */
	interface Permissions {
		const ACCESS_ADMIN_PAGE 	=	0b0001;
		const ACCESS_MOD_PAGE 		=	0b0010;
		const ACCESS_USER_PAGE 		=	0b0100;
	}
	interface UserType {
    	const ADMIN 	=	0b0111; // ADMIN_PAGE | MOD_PAGE | USER_PAGE
    	const READER 	=	0b0011; // 		      | MOD_PAGE | USER_PAGE
    	const WRITER 	=	0b0001; //                       | USER_PAGE
	}
	interface ExitCode {
    	const LOGIN_SUCCESS		=	0;
    	const LOGIN_INCORRECT	=	1;
    	const LOGIN_NOT_FOUND 	=	2;
	}
	interface PageFlag {
		const PASSWORD_CHANGED 			=	0;
		const OLD_PASSWORD_INCORRECT 	=	1;
	}
	/* Flagi inboxa */
	interface InboxFlag {
		const REFRESH_SUCCESS 	= 	0;
		const REFRESH_ERROR 	= 	1;

		const RENDER_RECEIVED 	=	2;
		const RENDER_SENT 		=	3;
	}
	/* Flagi ustawień */
	interface SettingFlag {
		const FILTER_SETTING 		=	1;	//	ustawienie widoczne z poziomu filtrów itp
		const BIT_FLAG 				=	2;  // 	jeśli nastawiona ta flaga to filtr w inboxie
	}
	interface SettingEnum {
		const MULTI_ENUM 	=	0;	//	do jednego enuma jedna wartość
		const SINGLE_ENUM 	=	1; 	//	do jednego enuma kilka wartości
	}
	abstract class SettingType {
		const BOOL_SETTING 	=	0;
		const INT_SETTING 	=	1;
		const STR_SETTING 	=	2;
		/* Parsery typów */
		public static $TYPES	=	[ 
			self::BOOL_SETTING	=> 'boolval',
			self::INT_SETTING 	=> 'intval',
			self::STR_SETTING 	=> 'strval'
		];
	}
}
?>
