<?php
namespace Sync;

use Redaxscript\Filter;
use Redaxscript\Html;
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
		/* html element */

		$element = new Html\Element();
		$listElement = $element
			->copy()
			->init('ul',
			[
				'class' => 'rs-list-default'
			]);
		$itemElement = $element->copy()->init('li');

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
		$output = null;
		$outputHead = null;
		$outputBody = null;
		$outputFoot = null;
		$tableArray =
		[
			'property' => $this->_language->get('property'),
			'type' => $this->_language->get('type'),
			'visibility' => $this->_language->get('visibility'),
			'description' => $this->_language->get('description')
		];

		/* html element */

		$element = new Html\Element();
		$titleElement = $element
			->copy()
			->init('h3',
			[
				'class' => 'rs-title-content-sub'
			])
			->text($this->_language->get('properties'));
		$wrapperElement = $element
			->copy()
			->init('div',
			[
				'class' => 'rs-wrapper-table'
			]);
		$tableElement = $element
			->copy()
			->init('table',
			[
				'class' => 'rs-table-default'
			]);
		$theadElement = $element->copy()->init('thead');
		$tbodyElement = $element->copy()->init('tbody');
		$tfootElement = $element->copy()->init('tfoot');
		$thElement = $element->copy()->init('th');
		$trElement = $element->copy()->init('tr');
		$tdElement = $element->copy()->init('td');

		/* process table */

		foreach ($tableArray as $key => $value)
		{
			$outputHead .= $thElement->copy()->text($value);
			$outputFoot .= $tdElement->copy()->text($value);
		}

		/* collect body output */

		if ($item->property)
		{
			foreach ($item->property as $key => $value)
			{
				if (strlen($value->name))
				{
					$outputBody .= $trElement
						->copy()
						->html(
							$tdElement->copy()->text($value->name) .
							$tdElement->copy()->text($value->docblock->tag->type) .
							$tdElement->copy()->text($value->attributes()->visibility) .
							$tdElement->copy()->text($value->docblock->description)
						);
				}
			}
		}
		else
		{
			$outputBody .= $trElement
				->copy()
				->html(
					$tdElement
						->copy()
						->attr('colspan', count($tableArray))
						->text($this->_language->get('property_no'))
				);
		}

		/* collect output */

		$outputHead = $theadElement->html(
			$trElement->html($outputHead)
		);
		$outputBody = $tbodyElement->html($outputBody);
		$outputFoot = $tfootElement->html(
			$trElement->html($outputFoot)
		);
		$output .= $titleElement . $wrapperElement->copy()->html(
			$tableElement->html($outputHead . $outputBody . $outputFoot)
		);
		return $output;
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
		$output = null;
		$outputHead = null;
		$outputBody = null;
		$outputFoot = null;
		$tableArray =
		[
			'method' => $this->_language->get('method'),
			'visibility' => $this->_language->get('visibility'),
			'description' => $this->_language->get('description')
		];

		/* html element */

		$element = new Html\Element();
		$titleElement = $element
			->copy()
			->init('h3',
			[
				'class' => 'rs-title-content-sub'
			])
			->text($this->_language->get('properties'));
		$wrapperElement = $element
			->copy()
			->init('div',
			[
				'class' => 'rs-wrapper-table'
			]);
		$tableElement = $element
			->copy()
			->init('table',
			[
				'class' => 'rs-table-default'
			]);
		$theadElement = $element->copy()->init('thead');
		$tbodyElement = $element->copy()->init('tbody');
		$tfootElement = $element->copy()->init('tfoot');
		$thElement = $element->copy()->init('th');
		$trElement = $element->copy()->init('tr');
		$tdElement = $element->copy()->init('td');

		/* process table */

		foreach ($tableArray as $key => $value)
		{
			$outputHead .= $thElement->copy()->text($value);
			$outputFoot .= $tdElement->copy()->text($value);
		}

		/* collect body output */

		if ($item->method)
		{
			foreach ($item->method as $key => $value)
			{
				if (strlen($value->name))
				{
					$outputBody .= $trElement
						->copy()
						->html(
							$tdElement->copy()->text($value->name) .
							$tdElement->copy()->text($value->attributes()->visibility) .
							$tdElement->copy()->text($value->docblock->description)
						);
				}
			}
		}
		else
		{
			$outputBody .= $trElement
				->copy()
				->html(
					$tdElement
						->copy()
						->attr('colspan', count($tableArray))
						->text($this->_language->get('method_no'))
				);
		}

		/* collect output */

		$outputHead = $theadElement->html(
			$trElement->html($outputHead)
		);
		$outputBody = $tbodyElement->html($outputBody);
		$outputFoot = $tfootElement->html(
			$trElement->html($outputFoot)
		);
		$output .= $titleElement . $wrapperElement->copy()->html(
			$tableElement->html($outputHead . $outputBody . $outputFoot)
		);
		return $output;
	}
}
