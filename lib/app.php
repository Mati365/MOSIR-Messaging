<?php
namespace App {
    opcache_reset();
    
    include 'cache.php';
	include 'login.php';
    include 'messages.php';
    include 'inbox.php';
    include 'page.php';
    
	class App extends \ArrayObject {
        public static $VERSION   =   'ver. 1.0';

		/* Rejestrowanie dodatków z klas */
        private $module_exts     =   [ ];
        public function regExt($name, $callback) {
            $this->module_exts[$name] = $callback;
        }
        public function __call($method, $args) {
            $func = $this->module_exts[$method];
            if (isset($func)) {
                array_unshift($args, $this);
                return call_user_func_array($func, $args);
            } 
        }
        /* Przesłonienie domyślnych operatorów */
		public function offsetSet($i, $v) {
			if(!$v instanceof \Core\Module)
				throw new \Exception('Object is not app module!');
        	/* Rejestrowanie i inicjacja */
            $v->init($this);
            foreach($v->app_exts as $method_name => $callback)
                $this->regExt($method_name, $callback);
        	parent::offsetSet($v->getModuleName(), $v);
        	return $v;
    	}
    	public function offsetGet($i) {
    		return parent::offsetExists($i) ? 
    						parent::offsetGet($i) :
    						parent::offsetSet($i, new $i);
    	}
    	/* Nasłuchiwanie POSTa */
    	public function init() {
    		if(!isset($_POST['func']))
    			return;

            $sql = $this->sql();
            foreach($_POST as $key => $val) {
                unset($_POST[$key]);
                $_POST[$sql->safeString($key)] = $sql->safeString($val);
            }
            
            header("Content-Type: application/json", true);
    		echo json_encode(
    				$this
    				->offsetGet('Core\\'.$_POST['func'])
    				->response($_POST, $_POST['action'])
    			);
    	}
	}
	/* Główna metoda aplikacji */
	$app = new App;
	$app[] = new \Core\SQLwrapper(new \Core\Config);
	$app[] = new \Core\LoginManager;
    $app[] = new \Core\MessageManager;
    $app[] = new \Core\SettingsManager;
    $app[] = new \Core\Inbox;
    $app[] = new \Core\Page;
    $app[] = new \Core\PairSettings;
    $app[] = new \Core\SettingsPage;
	$app->init();
}
?>