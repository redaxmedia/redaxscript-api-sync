<?php
namespace Doc;

use Redaxscript\Filter;

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
		ob_start();
		var_dump($item->class->name);
		var_dump($item->class->extends);
		var_dump($item->class->attributes());
		var_dump($item->class->docblock);
		return '<blockcode>' . ob_get_clean() . '</blockcode>';
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
		ob_start();
		var_dump($item->class->property);
		return '<blockcode>' . ob_get_clean() . '</blockcode>';
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

		ob_start();
		var_dump($item->class->method);
		return '<blockcode>' . ob_get_clean() . '</blockcode>';
	}
}
