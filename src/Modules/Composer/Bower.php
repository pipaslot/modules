<?php
namespace Pipas\Modules\Composer;

use Composer\Package\PackageInterface;
use Composer\Script\Event;

/**
 * Call the installation dependencies of front-end bower tool. Using extra section of composer config file.
 *
 * Example of using:
 * "extra":{
 *    "bower":{
 *        "files": [
 *            "relative/path/to/bower.json"
 *        ],
 *        "dependencies": [
 *            "bower-dependency": "~1.0.0",
 *        ]
 *    }
 * }
 *
 * @author Petr Štipek <p.stipek@email.cz>
 */
class Bower
{
	private static $installed = array();

	/**
	 * Call bower install in same folder as composer
	 * @param Event $event
	 */
	public static function install(Event $event)
	{
		passthru("bower install");
	}

	/**
	 * Install package
	 * @param $package
	 * @param $version
	 */
	public static function installPackage($package, $version)
	{
		$flags = "$package#$version";
		if (isset(self::$installed[$flags])) return;
		passthru("bower install $flags");
		self::$installed[$flags] = true;
	}

	/**
	 * Install bower dependencies defined into section extra: bower: dependencies: []
	 * @param Event $event
	 */
	public static function installDependencies(Event $event)
	{
		$extra = $event->getComposer()->getPackage()->getExtra();
		if (isset($extra['bower']['dependencies']) AND is_array($extra['bower']['dependencies'])) {
			foreach ($extra['bower']['dependencies'] as $package => $version) {
				self::installPackage($package, $version);
			}
		}
	}

	/**
	 * Install bower files defined in property extra: bower: files: []
	 * @param Event $event
	 */
	public static function installFiles(Event $event)
	{
		$composer = $event->getComposer();

		self::runExtras($composer->getPackage());
		foreach ($composer->getRepositoryManager()->getLocalRepository()->getPackages() as $package) {
			self::runExtras($package);
		}
	}

	/**
	 * Parse Composer extra section for files
	 * @param PackageInterface $package
	 */
	private static function runExtras(PackageInterface $package)
	{
		$extra = $package->getExtra();
		if (!isset($extra['bower'])) return;
		//Load nad parse bower files
		if (isset($extra['bower']['files']) AND is_array($extra['bower']['files'])) {
			foreach ($extra['bower']['files'] as $file) {
				$path = getcwd() . "/" . $file;
				if (!is_file($path)) throw new \OutOfRangeException("File $file defined in composer section extra.bower.files does not exist on path: $path");
				$bower = json_decode(file_get_contents($path));
				if (isset($bower->dependencies)) {
					foreach ($bower->dependencies as $package => $version) {
						self::installPackage($package, $version);
					}
				}
			}
		}
	}

}