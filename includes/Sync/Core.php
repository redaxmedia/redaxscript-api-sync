<?php
namespace Sync;

use Redaxscript\Config;
use Redaxscript\Db;
use Redaxscript\Html;
use Redaxscript\Language;
use Redaxscript\Reader;

/**
 * parent class to sync the documentation
 *
 * @since 4.0.0
 *
 * @package Sync
 * @category Core
 * @author Henry Ruhs
 */

class Core
{
	/**
	 * instance of the language class
	 *
	 * @var Language
	 */

	protected $_language;

	/**
	 * instance of the config class
	 *
	 * @var Config
	 */

	protected $_config;

	/**
	 * constructor of the class
	 *
	 * @since 4.0.0
	 *
	 * @param Language $language instance of the language class
	 * @param Config $config instance of the config class
	 */

	public function __construct(Language $language, Config $config)
	{
		$this->_language = $language;
		$this->_config = $config;
	}

	/**
	 * run
	 *
	 * @since 4.0.0
	 */

	public function run()
	{
		Db::getStatus() === 2 ? exit($this->_process()) : exit($this->_language->get('database_failed') . PHP_EOL);
	}

	/**
	 * process
	 *
	 * @since 4.0.0
	 *
	 * @return int
	 */

	protected function _process() : int
	{
		$parser = new Parser($this->_language);
		$reader = new Reader();
		$structureXML = $reader->loadXML('build' . DIRECTORY_SEPARATOR . 'structure.xml')->getObject();
		$author = 'api-sync';
		$categoryCounter = $parentId = 2000;
		$articleCounter = 2000;
		$status = 0;

		/* html elements */

		$textElement = new Html\Element();
		$textElement->init('p');

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
				'title' => 'Introduction',
				'alias' => 'introduction-' . $articleCounter,
				'author' => $author,
				'text' => $textElement->text($this->_language->get('introduction_api') . $this->_language->get('point')),
				'rank' => $articleCounter,
				'category' => $categoryCounter
			])
			->save();

		/* process xml */

		foreach ($structureXML as $key => $value)
		{
			if ($key === 'file')
			{
				$categoryTitle = $parser->getNamespace($value);
				$categoryAlias = $parser->getNamespaceAlias($value);
				$categoryId = Db::forTablePrefix('categories')->where('alias', $categoryAlias)->findOne()->id;
				$articleTitle = $parser->getName($value);
				$articleAlias = $parser->getNameAlias($value);
				$articleText = $parser->getContent($value);

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

				/* else create article */

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

		$this->_setAutoIncrement(3000);
		return $status;
	}

	/**
	 * set the auto increment
	 *
	 * @since 4.0.0
	 *
	 * @param int $increment
	 */

	protected function _setAutoIncrement(int $increment = 0)
	{
		Db::rawExecute('ALTER TABLE ' . $this->_config->get('dbPrefix') . 'categories AUTO_INCREMENT = ' . $increment);
		Db::rawExecute('ALTER TABLE ' . $this->_config->get('dbPrefix') . 'articles AUTO_INCREMENT = ' . $increment);
	}
}
