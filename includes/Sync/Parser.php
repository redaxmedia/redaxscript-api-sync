<?php
namespace Sync;

use Redaxscript\Html;
use Redaxscript\Filter;
use Redaxscript\Language;
use SimpleXMLElement;

/**
 * parent class to parse the documentation
 *
 * @since 4.0.0
 *
 * @package Sync
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
	 * @since 4.0.0
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
	 * @since 4.0.0
	 *
	 * @param SimpleXMLElement $item
	 *
	 * @return string
	 */

	public function getNamespace(SimpleXMLElement $item = null) : string
	{
		$itemChildren = $item->class ? $item->class : $item->interface;
		return $itemChildren->attributes()->namespace;
	}

	/**
	 * get the namespace alias
	 *
	 * @since 4.0.0
	 *
	 * @param SimpleXMLElement $item
	 *
	 * @return string
	 */

	public function getNamespaceAlias(SimpleXMLElement $item = null) : string
	{
		$aliasFilter = new Filter\Alias();
		return strtolower($aliasFilter->sanitize($this->getNamespace($item)));
	}

	/**
	 * get the name
	 *
	 * @since 4.0.0
	 *
	 * @param SimpleXMLElement $item
	 *
	 * @return string
	 */

	public function getName(SimpleXMLElement $item = null) : string
	{
		$itemChildren = $item->class ? $item->class : $item->interface;
		return $itemChildren->name;
	}

	/**
	 * get the alias
	 *
	 * @since 4.0.0
	 *
	 * @param SimpleXMLElement $item
	 *
	 * @return string
	 */

	public function getNameAlias(SimpleXMLElement $item = null) : string
	{
		$aliasFilter = new Filter\Alias();
		return strtolower($aliasFilter->sanitize($this->getName($item)));
	}

	/**
	 * get the content
	 *
	 * @since 4.0.0
	 *
	 * @param SimpleXMLElement $item
	 *
	 * @return string
	 */

	public function getContent(SimpleXMLElement $item = null) : string
	{
		$itemChildren = $item->class ? $item->class : $item->interface;
		return $this->_renderList($itemChildren) . $this->_renderProperty($itemChildren) . $this->_renderMethod($itemChildren);
	}

	/**
	 * render the list
	 *
	 * @since 4.0.0
	 *
	 * @param SimpleXMLElement $item
	 *
	 * @return string
	 */

	protected function _renderList(SimpleXMLElement $item = null) : string
	{
		/* html elements */

		$listElement = new Html\Element();
		$listElement->init('ul',
		[
			'class' => 'rs-list-default'
		]);
		$itemElement = new Html\Element();
		$itemElement->init('li');

		/* collect item output */

		if ($item->attributes()->namespace)
		{
			$listElement->append(
				$itemElement->text($this->_language->get('namespace') . $this->_language->get('colon') . ' ' . $item->attributes()->namespace)
			);
		}
		if ($item->docblock->description)
		{
			$listElement->append(
				$itemElement->text($this->_language->get('description') . $this->_language->get('colon') . ' ' . $item->docblock->description)
			);
		}

		/* process tag */

		foreach ($item->docblock->tag as $key => $value)
		{
			$name = $this->_language->get((string)$value->attributes()->name);
			$description = $value->attributes()->description;
			if ($name && $description)
			{
				$listElement->append(
					$itemElement->text($name . $this->_language->get('colon') . ' ' . $description)
				);
			}
		}
		return $listElement;
	}

	/**
	 * render the property
	 *
	 * @since 4.0.0
	 *
	 * @param SimpleXMLElement $item
	 *
	 * @return string
	 */

	protected function _renderProperty(SimpleXMLElement $item = null) : string
	{
		/* html elements */

		$titleElement = new Html\Element();
		$titleElement->init('h3',
		[
			'class' => 'rs-title-content-sub'
		])
		->text($this->_language->get('properties'));
		$wrapperElement = new Html\Element();
		$wrapperElement->init('div',
		[
			'class' => 'rs-wrapper-table'
		]);
		$tableElement = new Html\Element();
		$tableElement->init('table',
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
				if (strlen($value->name))
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
						$trElement->append(
							$tdElement->clear()->text($text)
						);
					}
					$tbodyElement->append($trElement);
				}
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
		return $titleElement . $wrapperElement;
	}

	/**
	 * render the method
	 *
	 * @since 4.0.0
	 *
	 * @param SimpleXMLElement $item
	 *
	 * @return string
	 */

	protected function _renderMethod(SimpleXMLElement $item = null) : string
	{
		/* html elements */

		$titleElement = new Html\Element();
		$titleElement->init('h3',
		[
			'class' => 'rs-title-content-sub'
		])
		->text($this->_language->get('methods'));
		$wrapperElement = new Html\Element();
		$wrapperElement->init('div',
		[
			'class' => 'rs-wrapper-table'
		]);
		$tableElement = new Html\Element();
		$tableElement->init('table',
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
				if (strlen($value->name))
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
						$trElement->append(
							$tdElement->clear()->text($text)
						);
					}
					$tbodyElement->append($trElement);
				}
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
		return $titleElement . $wrapperElement;
	}
}
