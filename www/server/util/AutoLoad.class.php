<?php

class AutoLoad{
    
    static private 
	    $paths = [
	        'S' => '.',
	        'U' => 'util',
	        'M' => 'model',
	        'E' => 'entity',
	        'Ex' => 'exception',
	        'default' => 'util'
	    ],
	    $root = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    
    static public function loadClass($class_name){
        $parts = explode('_', $class_name);

        $dir = isset(self::$paths[$parts[0]])? self::$paths[$parts[0]]: self::$paths['default'];

        $way = self::$root . "{$dir}" . DIRECTORY_SEPARATOR . "{$class_name}.class.php";

        if( file_exists($way) )
            include_once( $way );
        else
            throw new Exception( 'autoload didnt find: ' . $way);
            
    }

    static public function clear(){
    	$autoload_funcs = spl_autoload_functions();

		foreach($autoload_funcs as $autoload_func)
		    spl_autoload_unregister($autoload_func);
    }

    static public function register(){
    	spl_autoload_extensions('.class.php');
		spl_autoload_register('AutoLoad::loadClass');
    }
    
}

?>