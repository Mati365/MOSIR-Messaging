<?php
namespace Core {
	class Module extends \ArrayObject {
		protected 	$app 		= 	null;
		public 		$app_exts 	=	[];
		/* 
		 * W razie POSTa zbiera argument func i wywołuje
		 * @param 	$data 	Dane POST
		 */
		public function response($data, $type) {}
		/*
		 * Inicjacja modułu przez aplikacje
		 * @param 	$app 	Uchwyt aplikacji
		 */
		public function init($app) {
			$this->app = $app;
		}
		/* Informacje nt. modułu */	
		public function getModuleName() {
			return get_class($this);
		}
		public function getApp() {
			return $this->app;
		} 
	}
}
?>