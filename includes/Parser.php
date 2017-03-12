<?php
namespace Doc;

use Redaxscript\Html;
use Redaxscript\Filter;
use Redaxscript\Language;

/**
 * parent class to parse the documentation
 *
 * @since 3.0.0
 *
 * @package Doc
 * @category Parser
 * @author Henry Ruhs
 */

class Parser
{
	/**
	 * instance of the language class
	 *
	 * @var object
	 */

	protected $_language;

	/**
	 * constructor of the class
	 *
	 * @since 3.0.0
	 *
	 * @param Language $language instance of the language class
	 */

	public function __construct(Language $language)
	{
		$this->_language = $language;
	}

	/**
	 * get the namespace
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */

	public function getNamespace($item = null)
	{
		$itemChildren = $item->class ? $item->class : $item->interface;
		return $itemChildren->attributes()->namespace;
	}

	/**
	 * get the namespace alias
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */

	public function getNamespaceAlias($item = null)
	{
		$aliasFilter = new Filter\Alias();
		return strtolower($aliasFilter->sanitize($this->getNamespace($item)));
	}

	/**
	 * get the name
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */

	public function getName($item = null)
	{
		$itemChildren = $item->class ? $item->class : $item->interface;
		return $itemChildren->name;
	}

	/**
	 * get the alias
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */

	public function getNameAlias($item = null)
	{
		$aliasFilter = new Filter\Alias();
		return strtolower($aliasFilter->sanitize($this->getName($item)));
	}

	/**
	 * get the content
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */

	public function getContent($item = null)
	{
		$itemChildren = $item->class ? $item->class : $item->interface;
		return $this->_renderList($itemChildren) . $this->_renderProperty($itemChildren) . $this->_renderMethod($itemChildren);
	}

	/**
	 * render the list
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */

	protected function _renderList($item = null)
	{
		/* html elements */

		$listElement = new Html\Element();
		$listElement
			->init('ul',
			[
				'class' => 'rs-list-default'
			]);

		/* collect item output */

		if ($item->attributes()->namespace)
		{
			$listElement->append('<li>' . $this->_language->get('namespace') . $this->_language->get('colon') . ' ' . $item->attributes()->namespace .'</li>');
		}
		if ($item->docblock->description)
		{
			$listElement->append('<li>' . $this->_language->get('description') . $this->_language->get('colon') . ' ' . $item->docblock->description .'</li>');
		}

		/* process tag */

		foreach ($item->docblock->tag as $key => $value)
		{
			$name = $this->_language->get((string)$value->attributes()->name);
			$description = $value->attributes()->description;
			if ($name && $description)
			{
				$listElement->append('<li>' . $name . $this->_language->get('colon') . ' ' . $description .'</li>');
			}
		}

		/* collect output */

		$output = $listElement;
		return $output;
	}

	/**
	 * render the property
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */

	protected function _renderProperty($item = null)
	{
		/* html elements */

		$titleElement = new Html\Element();
		$titleElement
			->init('h3',
			[
				'class' => 'rs-title-content-sub'
			])
			->text($this->_language->get('properties'));
		$wrapperElement = new Html\Element();
		$wrapperElement
			->init('div',
			[
				'class' => 'rs-wrapper-table'
			]);
		$tableElement = new Html\Element();
		$tableElement
			->init('table',
			[
				'class' => 'rs-table-default'
			]);
		$theadElement = new Html\Element();
		$theadElement->init('thead');
		$tbodyElement = new Html\Element();
		$tbodyElement->init('tbody');
		$tfootElement = new Html\Element();
		$tfootElement->init('tfoot');
		$thElement = new Html\Element();
		$thElement->init('th');
		$trElement = new Html\Element();
		$trElement->init('tr');
		$tdElement = new Html\Element();
		$tdElement->init('td');

		/* collect thead output */

		$theadElement
			->html(
				$trElement
					->copy()
					->html(
						$thElement->copy()->text($this->_language->get('property')) .
						$thElement->copy()->text($this->_language->get('type')) .
						$thElement->copy()->text($this->_language->get('visibility')) .
						$thElement->copy()->text($this->_language->get('description'))
					)
			);

		/* collect tfoot output */

		$tfootElement
			->html(
				$trElement
					->copy()
					->html(
						$tdElement->copy()->text($this->_language->get('property')) .
						$tdElement->copy()->text($this->_language->get('type')) .
						$tdElement->copy()->text($this->_language->get('visibility')) .
						$tdElement->copy()->text($this->_language->get('description'))
					)
			);

		/* collect body output */

		if ($item->property)
		{
			foreach ($item->property as $key => $value)
			{
				$bodyArray =
				[
					$value->name,
					$value->docblock->tag->type,
					$value->attributes()->visibility,
					$value->docblock->description
				];
				$trElement->clear();

				/* process body */

				foreach ($bodyArray as $text)
				{
					$tdElement
						->clear()
						->text($text);
					$trElement->append($tdElement);
				}
				$tbodyElement->append($trElement);
			}
		}
		else
		{
			$trElement->append(
				$tdElement
					->attr('colspan', 4)
					->text($this->_language->get('property_no') . $this->_language->get('point'))
			);
			$tbodyElement->append($trElement);
		}

		/* collect table output */

		$wrapperElement->html(
			$tableElement->html($theadElement .	$tbodyElement .	$tfootElement)
		);

		/* collect output */

		$output = $titleElement . $wrapperElement;
		return $output;
	}

	/**
	 * render the method
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */

	protected function _renderMethod($item = null)
	{
		/* html elements */

		$titleElement = new Html\Element();
		$titleElement
			->init('h3',
			[
				'class' => 'rs-title-content-sub'
			])
			->text($this->_language->get('methods'));
		$wrapperElement = new Html\Element();
		$wrapperElement
			->init('div',
			[
				'class' => 'rs-wrapper-table'
			]);
		$tableElement = new Html\Element();
		$tableElement
			->init('table',
			[
				'class' => 'rs-table-default'
			]);
		$theadElement = new Html\Element();
		$theadElement->init('thead');
		$tbodyElement = new Html\Element();
		$tbodyElement->init('tbody');
		$tfootElement = new Html\Element();
		$tfootElement->init('tfoot');
		$thElement = new Html\Element();
		$thElement->init('th');
		$trElement = new Html\Element();
		$trElement->init('tr');
		$tdElement = new Html\Element();
		$tdElement->init('td');

		/* collect thead output */

		$theadElement
			->html(
				$trElement
					->copy()
					->html(
						$thElement->copy()->text($this->_language->get('method')) .
						$thElement->copy()->text($this->_language->get('visibility')) .
						$thElement->copy()->text($this->_language->get('description'))
					)
			);

		/* collect tfoot output */

		$tfootElement
			->html(
				$trElement
					->copy()
					->html(
						$tdElement->copy()->text($this->_language->get('method')) .
						$tdElement->copy()->text($this->_language->get('visibility')) .
						$tdElement->copy()->text($this->_language->get('description'))
					)
			);

		/* collect body output */

		if ($item->method)
		{
			foreach ($item->method as $key => $value)
			{
				$bodyArray =
				[
					$value->name,
					$value->attributes()->visibility,
					$value->docblock->description
				];
				$trElement->clear();

				/* process body */

				foreach ($bodyArray as $text)
				{
					$tdElement
						->clear()
						->text($text);
					$trElement->append($tdElement);
				}
				$tbodyElement->append($trElement);
			}
		}
		else
		{
			$trElement->append(
				$tdElement
					->attr('colspan', 3)
					->text($this->_language->get('method_no') . $this->_language->get('point'))
			);
			$tbodyElement->append($trElement);
		}

		/* collect table output */

		$wrapperElement->html(
			$tableElement->html($theadElement .	$tbodyElement .	$tfootElement)
		);

		/* collect output */

		$output = $titleElement . $wrapperElement;
		return $output;
	}
}
