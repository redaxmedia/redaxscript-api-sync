<?php
namespace Sync;

use Redaxscript\Admin;
use Redaxscript\Dater;
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
	 * constructor of the class
	 *
	 * @since 4.0.0
	 *
	 * @param Language $language instance of the language class
	 */

	public function __construct(Language $language)
	{
		$this->_language = $language;
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
		$dater = new Dater();
		$dater->init();
		$now = $dater->getDateTime()->getTimestamp();
		$categoryModel = new Admin\Model\Category();
		$articleModel = new Admin\Model\Article();
		$parser = new Parser($this->_language);
		$reader = new Reader();
		$structureObject = $reader->loadXML('build' . DIRECTORY_SEPARATOR . 'structure.xml')->getObject();
		$author = 'api-sync';
		$categoryCounter = 2000;
		$parentId = 2000;
		$articleCounter = 2000;
		$status = 0;

		/* html elements */

		$textElement = new Html\Element();
		$textElement
			->init('p')
			->text($this->_language->get('introduction_api') . $this->_language->get('point'));

		/* delete first */

		$categoryModel->query()->where('author', $author)->deleteMany();
		$articleModel->query()->where('author', $author)->deleteMany();

		/* create category */

		$categoryModel->createByArray(
		[
			'id' => $categoryCounter,
			'title' => 'API',
			'alias' => 'api',
			'author' => $author,
			'rank' => $categoryCounter,
			'date' => $now
		]);

		/* create article */

		$articleModel->createByArray(
		[
			'id' => $articleCounter,
			'title' => 'Introduction',
			'alias' => 'introduction-' . $articleCounter,
			'author' => $author,
			'text' => $textElement,
			'rank' => $articleCounter,
			'category' => $categoryCounter,
			'date' => $now
		]);

		/* process structure */

		foreach ($structureObject as $value)
		{
			if ($value->class || $value->interface)
			{
				$categoryTitle = $parser->getNamespace($value);
				$categoryAlias = $parser->getNamespaceAlias($value);
				$categoryId = $categoryModel->getByAlias($categoryAlias)->id;
				$articleTitle = $parser->getName($value);
				$articleAlias = $parser->getNameAlias($value);
				$articleText = $parser->getContent($value);

				/* create category */

				if (!$categoryId)
				{
					$categoryModel->createByArray(
					[
						'id' => ++$categoryCounter,
						'title' => $categoryTitle,
						'alias' => $categoryAlias,
						'author' => $author,
						'rank' => $categoryCounter,
						'parent' => $parentId,
						'date' => $now
					]);
				}

				/* else create article */

				$createStatus = $articleModel->createByArray(
				[
					'id' => ++$articleCounter,
					'title' => $articleTitle,
					'alias' => $articleAlias . '-' . $articleCounter,
					'author' => $author,
					'text' => $articleText,
					'rank' => $articleCounter,
					'category' => $categoryId ? : $categoryCounter,
					'date' => $now
				]);

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

		Db::setAutoIncrement('categories', 3000);
		Db::setAutoIncrement('articles', 3000);
		return $status;
	}
}
