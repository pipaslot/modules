<?php


namespace Pipas\Modules\Composer\Extra\Config;

/**
 * Example of configuration:
 * "media": {
 *    "web.config": {
 *        "source": "relative path",
 *        "target: "relative path",
 *    }
 *    "www-root": "temp",    //relative path to www-root
 *    "base-path": "media",    //path from www-root to media directory
 *    "directories":{
 *        "name-under-public-media-directory": "relative/path/to/private/media/directory"
 *    },
 *    "ignored":[
 *        "ignored-media-directory-of-parent-modules"
 *    ]
 * }
 *
 * @author Petr Štipek <p.stipek@email.cz>
 */
class MediaConfig
{
	const WEB_CONFIG = "web.config";
	/** @var string */
	protected $webConfigSource = self::WEB_CONFIG;
	/** @var string */
	protected $webConfigTarget = self::WEB_CONFIG;
	/** @var string */
	protected $wwwRoot;
	/** @var string */
	protected $basePath;
	/** @var array */
	protected $directories = array();
	/** @var array */
	protected $ignored = array();

	public function __construct($extra)
	{
		$media = $extra['media'];
		if (isset($media['web.config'])) {
			$this->webConfigTarget = isset($media['web.config']['target']) ? $media['web.config']['target'] : self::WEB_CONFIG;
			$this->webConfigSource = isset($media['web.config']['source']) ? $media['web.config']['source'] : $this->webConfigTarget;
		}
		if (isset($media['directories']) AND is_array($media['directories'])) {
			$this->directories = $media['directories'];
		}
		$this->wwwRoot = isset($media['www-root']) ? trim($media['www-root'], "\\/") : 'www';
		$this->basePath = isset($media['base-path']) ? trim($media['base-path'], "\\/") : 'media_modules';
		$this->ignored = isset($media['ignored']) ? (array)$media['ignored'] : array();
	}

	/**
	 * @return string
	 */
	public function getWebConfigSource()
	{
		return $this->webConfigSource;
	}

	/**
	 * @return string
	 */
	public function getWebConfigTarget()
	{
		return $this->webConfigTarget;
	}

	/**
	 * @return string
	 */
	public function getWwwRoot()
	{
		return $this->wwwRoot;
	}

	/**
	 * @return string
	 */
	public function getBasePath()
	{
		return $this->basePath;
	}

	/**
	 * @return array
	 */
	public function getDirectories()
	{
		return $this->directories;
	}

	/**
	 * @return array
	 */
	public function getIgnored()
	{
		return $this->ignored;
	}


}