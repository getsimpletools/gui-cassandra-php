<?php

ini_set('default_charset','UTF-8');
setlocale(LC_ALL, 'en_GB.UTF8');

ini_set("auto_detect_line_endings", 1);

ini_set('session.gc_maxlifetime', 3600*24*30);
session_set_cookie_params(3600*24*30);
session_start();

define('DS', DIRECTORY_SEPARATOR);


date_default_timezone_set(date_default_timezone_get());
define('APPLICATION_DIR',dirname(__FILE__).'/..');
define('MAIN_DIR',dirname(__FILE__).'/../..');
define('TEMP_DIR',dirname(__FILE__).'/../../temp');
define('IMG_DIR',dirname(__FILE__).'/../../public/img');
define('CONFIG_DIR',dirname(__FILE__));
define('LIB_DIR',dirname(__FILE__).'/../../lib');
define('LOGS_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'logs');
define('CSS_DIR',dirname(__FILE__).'/../../public/css');
define('JS_DIR',dirname(__FILE__).'/../../public/js');
define('VERSION','0.0.16');


define('ENV_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'_env' );
define('PUBLIC_DIR', __DIR__ . '/../../public');


$mainConfig = @require_once(ENV_DIR. DIRECTORY_SEPARATOR.'main.config.php');


define('APP_ENV', $mainConfig['ENV']);
if($mainConfig['ENV'] == 'DEV')
{
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
}
else
{
	error_reporting(0);
	ini_set("display_errors", 0);
}


require(dirname(__FILE__).'/../../vendor/autoload.php');



use Simpletools\Db\Cassandra\Client;


$router = \Simpletools\Mvc\Router::settings(array(
		"applicationDir" => APPLICATION_DIR,
));

//SimpleLayout::settings(array(
//		'path_to_layout'=>APPLICATION_DIR.'/layouts/main.php'
//));


$layout = \Simpletools\Page\Layout::settings(array(
		'layouts' => array(
				'default'		=> APPLICATION_DIR.'/layouts/default.phtml',
			//'default'		=> APPLICATION_DIR.'/layouts/vue.phtml'
		)
));



$cluster = require ENV_DIR . '/cassandra.config.php';
define('CLUSTER_HOST', $cluster['hosts'][0]);
define('CLUSTER_PORT', $cluster['port']);
define('CASSANDRA_USERNAME', $cluster['username']);
define('CASSANDRA_PASSWORD', $cluster['password']);

Client::cluster([
		'default' => true,
		'name' => 'local_cluster',
		'hosts' => $cluster['hosts'][0],
		'username' => $cluster['username'],
		'password' => $cluster['password'],
		'port' => $cluster['port'],
		'keyspace' => 'movie_guess'
]);

