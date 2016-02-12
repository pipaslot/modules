<?php


namespace Pipas\Modules\Templates;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class TemplateExtraParameters
{
	/** @var string */
	private $bowerDir;
	/** @var string */
	private $mediaDir;
	/** @var string */
	private $moduleDir;

	/**
	 * Bower component directory name or path from wwww root
	 * @param string $bowerDir
	 * @return $this
	 */
	public function setBowerDir($bowerDir)
	{
		$this->bowerDir = $bowerDir;
		return $this;
	}

	/**
	 * Media directory name or path from wwww root
	 * @param string $mediaDir
	 * @return $this
	 */
	public function setMediaDir($mediaDir)
	{
		$this->mediaDir = $mediaDir;
		return $this;
	}

	/**
	 * Modules media directory name or path from wwww root
	 * @param string $modulesDir
	 * @return $this
	 */
	public function setModuleDir($modulesDir)
	{
		$this->moduleDir = $modulesDir;
		return $this;
	}

	/**
	 * @param string $basePath
	 * @return string
	 */
	public function getBowerPath($basePath)
	{
		return $this->concat($basePath, $this->bowerDir);
	}

	/**
	 * @param string $basePath
	 * @return string
	 */
	public function getMediaPath($basePath)
	{
		return $this->concat($basePath, $this->mediaDir);
	}

	/**
	 * @param string $basePath
	 * @return string
	 */
	public function getModulePath($basePath)
	{
		return $this->concat($basePath, $this->moduleDir);
	}

	/**
	 * @param string $basePath
	 * @param string $path
	 * @return string
	 */
	private function concat($basePath, $path)
	{
		$concat = rtrim($basePath, '/\\') . '/' . trim($path, '/\\');
		return rtrim($concat, '/\\');
	}

}