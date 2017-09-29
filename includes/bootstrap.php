<?php
namespace Redaxscript;

error_reporting(E_ERROR | E_PARSE);

/* include */

include_once('vendor' . DIRECTORY_SEPARATOR . 'redaxmedia' . DIRECTORY_SEPARATOR . 'redaxscript' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'Autoloader.php');

/* autoload */

$autoloader = new Autoloader();
$autoloader->init(
[
	'Sync' => 'includes' . DIRECTORY_SEPARATOR . 'Sync',
	'Redaxscript' => 'vendor' . DIRECTORY_SEPARATOR . 'redaxmedia' . DIRECTORY_SEPARATOR . 'redaxscript' . DIRECTORY_SEPARATOR . 'includes',
	'vendor' . DIRECTORY_SEPARATOR . 'redaxmedia' . DIRECTORY_SEPARATOR . 'redaxscript' . DIRECTORY_SEPARATOR . 'libraries'
]);

/* get instance */

$config = Config::getInstance();

/* config */

$dbUrl = getenv('DB_URL');
$config->parse($dbUrl);

/* database */

Db::construct($config);
Db::init();

/* language */

$language = Language::getInstance();
$language->init();