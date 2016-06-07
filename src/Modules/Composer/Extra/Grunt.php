<?php
namespace Pipas\Modules\Composer\Extra;

use Composer\Package\PackageInterface;

/**
 * Installation of grunt and NPM packages defined in composer.json config file
 *
 * Example of using:
 * "extra":{
 *    "grunt": {
 *        "first/path": "",                // runs default task
 *        "second/path": "custom-task"
 *    }
 * }
 *
 * @author Petr Štipek <p.stipek@email.cz>
 * 
 * @deprecated
 */
class Grunt implements IExtra
{
	function run(PackageInterface $package, $isMain = true)
	{
		$extra = $package->getExtra();
		if (isset($extra['grunt']) AND is_array($extra['grunt'])) {
			foreach ($extra['grunt'] as $dir => $task) {
				$this->runTask($dir, $task);
			}
		}
	}

	/**
	 * Run grunt task into defined directory
	 * @param string $directory Relative path
	 * @param string $taskName
	 */
	public function runTask($directory = "", $taskName = "")
	{
		$path = getcwd() . "/" . trim($directory, "/\\");
		passthru("cd $path & npm install & grunt $taskName");
	}
}