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
$language = Language::getInstance();
$language->init();

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
	$DocParser = new Doc\Parser($language);
	$reader = new Reader();
	$structureXML = $reader->loadXML('build/structure.xml')->getObject();
	$author = 'api-sync';
	$categoryCounter = $parentId = 2000;
	$articleCounter = 2000;

	/* delete category and article */

	Db::forTablePrefix('categories')->where('author', $author)->deleteMany();
	Db::forTablePrefix('articles')->where('author', $author)->deleteMany();

	/* create category */

	Db::forTablePrefix('categories')
		->create()
		->set(
		[
			'id' => $categoryCounter,
			'title' => 'API',
			'alias' => 'api',
			'author' => $author
		])
		->save();

	/* create article */

	Db::forTablePrefix('articles')
		->create()
		->set(
		[
			'id' => $articleCounter,
			'title' => 'API Introduction',
			'alias' => 'api-introduction',
			'author' => $author,
			'text' => 'Welcome to Redaxscript\'s API.',
			'rank' => $articleCounter,
			'category' => $categoryCounter
		])
		->save();

	/* process xml */

	foreach ($structureXML as $key => $value)
	{
		if ($key === 'file')
		{
			$categoryTitle = $DocParser->getNamespace($value);
			$categoryAlias = $DocParser->getNamespaceAlias($value);
			$categoryId = Db::forTablePrefix('categories')->where('alias', $categoryAlias)->findOne()->id;
			$articleTitle = $DocParser->getName($value);
			$articleAlias = $DocParser->getNameAlias($value);
			$articleText = $DocParser->getContent($value);

			/* create category */

			if (!$categoryId)
			{
				Db::forTablePrefix('categories')
					->create()
					->set(
					[
						'id' => ++$categoryCounter,
						'title' => $categoryTitle,
						'alias' => $categoryAlias,
						'author' => $author,
						'rank' => $categoryCounter,
						'parent' => $parentId
					])
					->save();
			}

			/* create article */

			$createStatus = Db::forTablePrefix('articles')
				->create()
				->set(
				[
					'id' => ++$articleCounter,
					'title' => $articleTitle,
					'alias' => $articleAlias . '-' . $articleCounter,
					'author' => $author,
					'text' => $articleText,
					'rank' => $articleCounter,
					'category' => $categoryId ? $categoryId : $categoryCounter
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
