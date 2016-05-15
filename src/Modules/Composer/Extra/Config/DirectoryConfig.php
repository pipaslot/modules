<?php


namespace Pipas\Modules\Composer\Extra\Config;

/**
 * Example of configuration:
 * "directory": {
 *    "secure": [
 *        "relative-path"
 *    ],
 *    "create": [
 *        "relative-path"
 *    ],
 *    "empty": [
 *        "relative-path"
 *    ]
 * }
 *
 * @author Petr Štipek <p.stipek@email.cz>
 */
class DirectoryConfig
{
	/** @var array */
	protected $create = array();
	/** @var array */
	protected $empty = array();
	/** @var array */
	protected $secure = array();

	public function __construct($extra)
	{
		$dir = $extra['directory'];
		if (isset($dir['empty']) AND is_array($dir['empty'])) {
			$this->empty = $dir['empty'];
		}
		if (isset($dir['create']) AND is_array($dir['create'])) {
			$this->create = $dir['create'];
		}
		if (isset($dir['secure']) AND is_array($dir['secure'])) {
			$this->secure = $dir['secure'];
		}
	}

	/**
	 * @return array
	 */
	public function getCreate()
	{
		return $this->create;
	}

	/**
	 * @return array
	 */
	public function getEmpty()
	{
		return $this->empty;
	}

	/**
	 * @return array
	 */
	public function getSecure()
	{
		return $this->secure;
	}

}