<?php
namespace Core {
	include 'database.php';
	use \BitFlag\ExitCode;

	/* Mendżer sesji odpowiedzialny za logowanie */
	class LoginManager extends Module {
		private $logged_user = null;
		/*
		 * Wiadomość wysłana przez AJAX do modułu
		 * @param 	$data 	Dane już sparsowane JSON
		 */
		public function response($data, $type) {
			return [
				'exit_code' => $type === 'logout' ? 
										$this->logout() : 
										$this->login($data)
			];
		}
		public function init($app) {
			$this->app_exts = [
				'user' 		=>  function($self) {
					return $self->offsetGet('Core\LoginManager')
	                        	->getUser();
				},
				'admin' 	=>  function($self) {
					return $self->user()
	                        	->checkPermission(\BitFlag\UserType::ADMIN);
				},
				'userID' 	=> 	function($self) {
					return $self->user()->id;
				},
				'userType'  =>  function($self) {
					return $self->user()->type;
				}
			];
			parent::init($app);
			try {
				session_set_cookie_params(3600*24*31*12);
				session_start();
				if(!isset($_SESSION['logged']))
					return;
				/* Sprawdzanie poprawności logowania */
				session_regenerate_id(true);
				if($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'])
					$this->logout();
				else
					$this->logged_user = $this
									->app['Core\SQLwrapper']
									->getUserBy(
										[ 'id' => $_SESSION['id']
									]);
			} catch(Exception $e) {}
		}
		/*
		 * Logowanie do systemu
		 * @param 	$user 	Użytkownik
		 */
		public function login($data) {
			$exit_code  =    ExitCode::LOGIN_NOT_FOUND;
            $sql        =    $this->app->sql();
			try {
				$user = $sql->getUserBy([
					'login' => 	$data['login']
				]);
				if(isset($user)) {
					if($user->password === SqlWrapper::encryptPassword($data['password'])) {
						$exit_code = ExitCode::LOGIN_SUCCESS;
						$this->logged_user = $user;
						{
							$_SESSION['logged'] = true;
							$_SESSION['id'] 	= $user->id;
							$_SESSION['ip'] 	= $_SERVER['REMOTE_ADDR'];
						}
					} else
						$exit_code = ExitCode::LOGIN_INCORRECT;
				}
			} catch(\Exception $e) {}
			return $exit_code;
		}
		public function logout() {
			session_set_cookie_params(0);
			session_unset();
			session_destroy();
			return ExitCode::LOGIN_SUCCESS;
		}
		/* Zwracanie aktualnie zalogowanego użytkownika */
		public function getUser() {
			return $this->logged_user;
		}
		public function isLogged() {
			return $this->getUser() !== null;
		}
	}
}
?>