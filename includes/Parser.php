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
	 * get the title
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	
	public function getTitle($item = null)
	{
		return $item->attributes()->path;
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
	
	public function getAlias($item = null)
	{
		$aliasFilter = new Filter\Alias();
		return $aliasFilter->sanitize($item->attributes()->path);
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
		return $this->_getHeader($item) . $this->_getProperty($item) . $this->_getMethod($item);
	}

	/**
	 * get the header
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */

	protected function _getHeader($item = null)
	{
		/* html elements */

		$titleElement = new Html\Element();
		$titleElement
			->init('h3',
			[
				'class' => 'rs-title-content-sub'
			])
			->text($this->_language->get('class') . $this->_language->get('colon') . ' ' . $item->class->name);
		$listElement = new Html\Element();
		$listElement
			->init('ul',
			[
				'class' => 'rs-list-default'
			]);

		/* collect item output */

		if ($item->class->attributes()->namespace)
		{
			$listElement->append('<li>' . $this->_language->get('namespace') . $this->_language->get('colon') . ' ' . $item->class->attributes()->namespace .'</li>');
		}
		if ($item->class->docblock->description)
		{
			$listElement->append('<li>' . $this->_language->get('description') . $this->_language->get('colon') . ' ' . $item->class->docblock->description .'</li>');
		}

		/* process tag */

		foreach ($item->class->docblock->tag as $key => $value)
		{
			$name = $this->_language->get((string)$value->attributes()->name);
			$description = $value->attributes()->description;
			if ($name && $description)
			{
				$listElement->append('<li>' . $name . $this->_language->get('colon') . ' ' . $description .'</li>');
			}
		}

		/* collect output */

		$output = $titleElement . $listElement;
		return $output;
	}

	/**
	 * get the property
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */

	protected function _getProperty($item = null)
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
		$theadElement
			->init('thead')
			->html(
				'<tr>' .
					'<th>' . $this->_language->get('property') . '</th>' .
					'<th>' . $this->_language->get('type') . '</th>' .
					'<th>' . $this->_language->get('visibility') . '</th>' .
					'<th>' . $this->_language->get('description') . '</th>' .
				'</tr>'
			);
		$tbodyElement = new Html\Element();
		$tbodyElement->init('tbody');
		$tfootElement = new Html\Element();
		$tfootElement
			->init('tfoot')
			->html(
				'<tr>' .
					'<td>' . $this->_language->get('property') . '</td>' .
					'<th>' . $this->_language->get('type') . '</th>' .
					'<td>' . $this->_language->get('visibility') . '</td>' .
					'<td>' . $this->_language->get('description') . '</td>' .
				'</tr>'
			);
		$trElement = new Html\Element();
		$trElement->init('tr');

		/* collect body output */

		foreach ($item->class->property as $key => $value)
		{
			$propertyArray =
			[
				$value->name,
				$value->docblock->tag->type,
				$value->attributes()->visibility,
				$value->docblock->description
			];
			$trElement->clear();

			/* process value */

			foreach ($propertyArray as $property)
			{
				$trElement->append('<td>' . $property . '</td>');
			}
			$tbodyElement->append($trElement);
		}

		/* collect table output */

		$wrapperElement->html(
			$tableElement->html(
				$theadElement .
				$tbodyElement .
				$tfootElement
			)
		);

		/* collect output */

		$output = $titleElement . $wrapperElement;
		return $output;
	}

	/**
	 * get the method
	 *
	 * @since 3.0.0
	 *
	 * @param object $item
	 *
	 * @return string
	 */

	protected function _getMethod($item = null)
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
		$theadElement
			->init('thead')
			->html(
				'<tr>' .
					'<th>' . $this->_language->get('method') . '</th>' .
					'<th>' . $this->_language->get('visibility') . '</th>' .
					'<th>' . $this->_language->get('description') . '</th>' .
				'</tr>'
			);
		$tbodyElement = new Html\Element();
		$tbodyElement->init('tbody');
		$tfootElement = new Html\Element();
		$tfootElement
			->init('tfoot')
			->html(
				'<tr>' .
					'<td>' . $this->_language->get('method') . '</td>' .
					'<td>' . $this->_language->get('visibility') . '</td>' .
					'<td>' . $this->_language->get('description') . '</td>' .
				'</tr>'
			);
		$trElement = new Html\Element();
		$trElement->init('tr');

		/* collect body output */

		foreach ($item->class->method as $key => $value)
		{
			$methodyArray =
			[
				$value->name,
				$value->attributes()->visibility,
				$value->docblock->description
			];
			$trElement->clear();

			/* process value */

			foreach ($methodyArray as $methody)
			{
				$trElement->append('<td>' . $methody . '</td>');
			}
			$tbodyElement->append($trElement);
		}

		/* collect table output */

		$wrapperElement->html(
			$tableElement->html(
				$theadElement .
				$tbodyElement .
				$tfootElement
			)
		);

		/* collect output */

		$output = $titleElement . $wrapperElement;
		return $output;
	}
}
