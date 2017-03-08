<?php
namespace Redaxscript;

use Doc;

error_reporting(E_ERROR || E_PARSE);

/* autoload */

include_once('vendor/redaxmedia/redaxscript/includes/Autoloader.php');

/* init */

$autoloader = new Autoloader();
$autoloader->init(
[
	'Doc' => 'includes',
	'Redaxscript' => 'vendor/redaxmedia/redaxscript/includes',
	'vendor/redaxmedia/redaxscript/libraries'
]);
$DocParser = new Doc\Parser();

/* get instance */

$config = Config::getInstance();

/* status and config */

$status = 1;
$dbUrl = getenv('DB_URL');
$config->parse($dbUrl);

/* database */

Db::construct($config);
Db::init();

/* sync api */

if (Db::getStatus() === 2)
{
	$status = 0;
	$reader = new Reader();
	$structureObject = $reader->loadXML('build/structure.xml')->getObject();
	$author = 'api-sync';
	$categoryId = 2000;
	$articleId = 2000;

	/* delete */

	Db::forTablePrefix('categories')->whereIdIs($categoryId)->deleteMany();
	Db::forTablePrefix('articles')->where('category', $categoryId)->deleteMany();
	Db::forTablePrefix('categories')
		->create()
		->set(
		[
			'id' => $categoryId,
			'title' => 'API',
			'alias' => 'api',
			'author' => $author
		])
		->save();

	/* process directory */

	foreach ($structureObject as $key => $value)
	{
		if ($key === 'file')
		{
			$title = $DocParser->getTitle($value);
			$alias = $DocParser->getAlias($value);
			$content = $DocParser->getContent($value);

			/* create */

			$createStatus = Db::forTablePrefix('articles')
				->create()
				->set(
				[
					'id' => $articleId++,
					'title' => $title,
					'alias' => $alias,
					'author' => $author,
					'text' => $content,
					'rank' => $articleId,
					'category' => $categoryId
				])
				->save();

			/* handle status */

			if ($createStatus)
			{
				echo '.';
			}
			else
			{
				$status = 1;
				echo 'F';
			}
		}
	}
	echo PHP_EOL;

	/* auto increment */

	Db::rawInstance()->rawExecute('ALTER TABLE categories AUTO_INCREMENT = 3000');
	Db::rawInstance()->rawExecute('ALTER TABLE articles AUTO_INCREMENT = 3000');
}
exit($status);
