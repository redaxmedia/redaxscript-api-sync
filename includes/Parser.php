<?php
namespace Doc;

use Redaxscript\Filter;

/**
 * parent class to parse the documentation
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category PhpDoc
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
		ob_start();
		var_dump($item);
		return '<blockcode>' . ob_get_clean() . '</blockcode>';
	}
}
