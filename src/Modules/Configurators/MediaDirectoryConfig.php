<?php


namespace Pipas\Modules\Configurators;
use Nette\Utils\Strings;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class MediaDirectoryConfig implements IMediaDirectoryConfig
{
	/** @var string */
	private $path;
	/** @var string */
	private $name;

	/**
	 * @param string $path Absolute path
	 * @return $this
	 * @throws \OutOfRangeException
	 */
	function setPath($path)
	{
		if (!is_dir($path)) throw new \OutOfRangeException("Path $path must be existing directory");
		$this->path = $path;
		return $this;
	}

	/**
	 * @return string
	 */
	function getPath()
	{
		return $this->path;
	}

	/**
	 * Set target directory name into relative web path under root folder media
	 * @param string $name url folder name
	 * @return $this
	 * @throws \OutOfRangeException
	 */
	function setName($name)
	{
		$webalized = Strings::webalize($name);
		if ($name !== $webalized) throw new \OutOfRangeException("Name is not optimized for URL addresses. Use '$webalized' instead of '$name'.");
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	function getName()
	{
		return $this->name;
	}

	/**
	 * @throws \OutOfRangeException
	 */
	function validate()
	{
		if (!$this->name) throw new \OutOfRangeException("Property name does not be set");
		if (!$this->path) throw new \OutOfRangeException("Property path does not be set");
	}
}