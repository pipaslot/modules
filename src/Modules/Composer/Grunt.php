<?php
namespace Pipas\Modules\Composer;

use Composer\Script\Event;

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
 */
class Grunt
{
	/**
	 * Run tasks defined into section extra: grunt: []
	 * @param Event $event
	 */
	public static function run(Event $event)
	{
		$extra = $event->getComposer()->getPackage()->getExtra();
		if (isset($extra['grunt']) AND is_array($extra['grunt'])) {
			foreach ($extra['grunt'] as $dir => $task) {
				self::runTask($dir, $task);
			}
		}
	}

	/**
	 * Run grunt task into defined directory
	 * @param string $directory Relative path
	 * @param string $taskName
	 */
	public static function runTask($directory = "", $taskName = "")
	{
		$path = getcwd() . "/" . trim($directory, "/\\");
		passthru("cd $path & npm install & grunt $taskName");
	}
}