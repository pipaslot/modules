<?php


namespace Pipas\Modules\Configurators;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface IMediaDirectoryConfig
{
	/**
	 * @param string $path Absolute path
	 * @return $this
	 */
	function setPath($path);

	/**
	 * @return string
	 */
	function getPath();

	/**
	 * Set target directory name into relative web path under root folder media
	 * @param string $name url folder name
	 * @return $this
	 */
	function setName($name);

	/**
	 * @return string
	 */
	function getName();

	/**
	 * @throws \OutOfRangeException
	 */
	function validate();
}