<?php
namespace Core {
    /* Mała klasa obsługująca cache */
    class Cache {
        private static $cache = [ ];

        public static function exists($class_name, $variable) {
            return 
                array_key_exists($class_name, self::$cache) &&
                array_key_exists($variable, self::$cache[$class_name]);
        }
        public static function set($class_name, $array) {
            if(!self::exists($class_name, $array[0]))
                self::$cache[$class_name] = [];
            self::$cache[$class_name][$array[0]] = $array[1];
        }
        public static function get($class_name, $variable) {
            return self::$cache[$class_name][$variable];
        }
    }
}
?>