<?php
namespace Pipas\Modules\Composer;

use Composer\Package\PackageInterface;
use Composer\Script\Event;

/**
 * Tool accessing private directories with front-end libraries of modules
 *
 * Example of using:
 * "extra":{
 *    "media-directories":{
 *        "name-under-public-media-directory": "relative/path/to/private/media/directory"
 *    }
 * }
 *
 * For Unix will be created symlink to private directory.
 * For IIS will be updated web.config file and configuration will be passed between defined comments <!-- DynamicMediaDirectories --> and <!-- DynamicMediaDirectoriesEnd -->
 *
 * @author Petr Štipek <p.stipek@email.cz>
 */
class Media
{
	/** @var string Relative path to www root from directory with composer.json */
	private static $www = "www";
	/** @var string Path to media directory from URL */
	private static $mediaUrl = "media";
	const
		WEB_CONFIG_START_TAG = "<!-- DynamicMediaDirectories -->",
		WEB_CONFIG_END_TAG = "<!-- DynamicMediaDirectoriesEnd -->";

	public static function install(Event $event)
	{
		$composer = $event->getComposer();
		$rules = "";
		$rules .= self::createRules($composer->getPackage(), false);
		foreach ($composer->getRepositoryManager()->getLocalRepository()->getPackages() as $package) {
			$rules .= self::createRules($package);
		}
		$webConfigPath = getcwd() . "/web.config";
		self::applyRules($webConfigPath, $rules);

	}

	/**
	 * Create rules from package extras configuration
	 * @param PackageInterface $package
	 * @param bool $prefixVendor
	 * @return string
	 */
	private static function createRules(PackageInterface $package, $prefixVendor = true)
	{
		$extra = $package->getExtra();
		if (!isset($extra['media-directories']) OR !is_array($extra['media-directories'])) return "";
		$packageRoot = rtrim("vendor/" . $package->getName(), '/');
		$rules = "";
		foreach ($extra['media-directories'] as $name => $path) {
			//generate create config
			$modulePath = "/" . ($prefixVendor ? $packageRoot : "") . trim($path, '/') . "/";
			$modulePathAbsolute = getcwd() . $modulePath;

			if (!is_dir($modulePathAbsolute)) throw new \OutOfRangeException("Directory declared by relative path $path does not exist on absolute path $modulePathAbsolute");
			$rules .= "\n\t\t\t<rule name=\"Rewrite media modules to $modulePath folder\" stopProcessing=\"true\">"
				. "\n\t\t\t\t<match url=\"^" . self::$mediaUrl . "/$name/(.+)\" ignoreCase=\"false\" />"
				. "\n\t\t\t\t<action type=\"Rewrite\" url=\"$modulePath{R:1}\" />"
				. "\n\t\t\t</rule>";

			//create symlink for unix
			$mediaDir = getcwd() . '/' . self::$www . '/' . self::$mediaUrl;
			if (!is_dir($mediaDir)) throw new \OutOfRangeException("Media directory does not exist for expected path: $mediaDir");
			$module = $mediaDir . '/' . $name;
			if (!is_dir($module) AND !is_link($module)) {
				if (!@symlink($modulePathAbsolute, $module)) echo "Symlink was not created. Run command again with privileges of administrator\n";
				$htaccess = $modulePathAbsolute . "/.htaccess";
				if (!is_file($htaccess)) file_put_contents($htaccess, "Order Allow,Deny\nAllow from all");
			}
		}
		return $rules;
	}

	/**
	 * Apply rules to config file
	 * @param $webConfigPath
	 * @param $rules
	 */
	private static function applyRules($webConfigPath, $rules)
	{
		if (!is_file($webConfigPath)) {
			echo "IIS web.config file not found on path: $webConfigPath";
			return;
		}

		$content = file_get_contents($webConfigPath);
		$tagStart = strpos($content, self::WEB_CONFIG_START_TAG) + strlen(self::WEB_CONFIG_START_TAG);
		$tagEnd = strpos($content, self::WEB_CONFIG_END_TAG);
		$newContent = substr($content, 0, $tagStart)
			. $rules
			. "\n\t\t\t" . substr($content, $tagEnd, strlen($content) - $tagEnd);
		file_put_contents($webConfigPath, $newContent);
		echo "Updated web.config file: $webConfigPath\n";
	}

}