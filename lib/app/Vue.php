<?php

Namespace App;

class Vue
{
	protected static $_mainLib = APP_ENV == 'PRODUCTION'  ? '/js/dist/main.js' : '/js/dev/main.js';
	protected static $_vendor = APP_ENV == 'PRODUCTION'  ? '/js/dist/vendor.js' : '/js/dev/vendor.js';

	protected static $_settings = [
			'libPath' => '/js/libs/',
			'cssPath' => '/css/',
			'appPath' =>  APP_ENV == 'PRODUCTION'  ? '/js/dist/app/' : '/js/dev/app/',
			'componentPath' =>  APP_ENV == 'PRODUCTION'  ? '/js/dist/xComp/' : '/js/dev/xComp/',
	];
	protected static $_constants = [
			//'FO_API_URL'	=> FO_API_URL
	];
	protected static $_libs = [];
	protected static $_css = [];
	protected static $_components = [];

	public static function include($layout)
	{

		if($layout instanceof \Simpletools\Mvc\View)
		{
			$layout->controller = @$layout->_params['associative']['controller'];
			$layout->action = @$layout->_params['associative']['action'];
		}

		$output = '';

		if(self::$_constants )
		{
			$output.= '<script type="text/javascript">';
			foreach (self::$_constants  as $k => $v)
			{
				$output.= "var $k = '$v';";
			}
			$output.= '</script>';
		}

		$output.= '<script src="'.self::$_vendor.'?v='.VERSION.'" type="text/javascript"></script>';
		$output.= '<script src="'.self::$_mainLib.'?v='.VERSION.'" type="text/javascript"></script>';

		foreach (self::$_libs as $lib)
		{
			if(file_exists(PUBLIC_DIR.self::$_settings['libPath'].$lib))
				$output.= '<script src="'.self::$_settings['libPath'].$lib.'?v='.VERSION.'" type="text/javascript"></script>';
		}

		foreach (self::$_css as $css)
		{
			if(file_exists(PUBLIC_DIR.self::$_settings['cssPath'].$css))
				$output.= '<link rel="stylesheet" href="'.self::$_settings['cssPath'].$css.'?v='.VERSION.'">';
		}

		foreach (self::$_components as $component)
		{
			if(file_exists(PUBLIC_DIR.self::$_settings['componentPath'].$component.'/index.js'))
				$output.= '<script src="'.self::$_settings['componentPath'].$component.'/index.js'.'?v='.VERSION.'" type="text/javascript"></script>';
		}
		if(file_exists(PUBLIC_DIR.self::$_settings['appPath'].@$layout->controller.'/'.@$layout->action.'.js'))
		{
			$output.= '<script src="'.self::$_settings['appPath'].$layout->controller.'/'.$layout->action.'.js'.'?v='.VERSION.'" type="text/javascript"></script>';
		}
		elseif(file_exists(PUBLIC_DIR.self::$_settings['appPath'].@$layout->controller.'.js'))
		{
			$output.= '<script src="'.self::$_settings['appPath'].$layout->controller.'.js?v='.VERSION.''.'" type="text/javascript"></script>';
		}
//		elseif(file_exists(PUBLIC_DIR.self::$_settings['appPath'].'app.js'))
//		{
//			$output.= '<script src="'.self::$_settings['appPath'].'app.js'.'" type="text/javascript"></script>';
//		}

		echo $output;
	}

	public static function addLib($path)
	{
		if (is_array($path))
		{
			self::$_libs = array_merge(self::$_libs, $path);
		}
		elseif(!in_array($path,self::$_libs))
		{
			self::$_libs[] = $path;
		}
	}

	public static function addCss($path)
	{
		if (is_array($path))
		{
			self::$_css = array_merge(self::$_css, $path);
		}
		elseif(!in_array($path,self::$_css))
		{
			self::$_css[] = $path;
		}
	}

	public static function addComponent($name)
	{
		if (is_array($name))
		{
			self::$_components = array_merge(self::$_components, $name);
		}
		elseif(!in_array($name,self::$_components))
		{
			self::$_components[] = $name;
		}
	}
}
