<?php


//echo 'api';die;

	require_once(dirname(__FILE__)."/../application/config/config.php");
use \Simpletools\Store\Session;
use \Simpletools\Config\Ini;
use \Simpletools\Mvc\Model;
use \Simpletools\Http\Ssl;




if($router->getParam('controller') == 'api')
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
	header('Access-Control-Max-Age: 1000');
	header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

	$layout->start();

	$router->dispatch();

	$layout->controller 		= $router->getParam('controller');
	$layout->action 			= $router->getParam('action');

	$layout->render();
}
else
{
	require_once 'index.html';
}

?>
