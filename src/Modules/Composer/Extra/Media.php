<?php
namespace Pipas\Modules\Composer\Extra;

use Composer\Package\PackageInterface;

/**
 * Tool accessing private directories with front-end libraries of modules
 *
 * Example of using:
 * "media": {
 *    "www-root": "temp",    //relative path to www-root
 *    "base-path": "media",    //path from www-root to media directory
 *    "directories":{
 *        "name-under-public-media-directory": "relative/path/to/private/media/directory"
 *    }
 * }
 *
 * For Unix will be created symlink to private directory.
 * For IIS will be updated web.config file and configuration will be passed between defined comments <!-- DynamicMediaDirectories --> and <!-- DynamicMediaDirectoriesEnd -->
 *
 * @author Petr Štipek <p.stipek@email.cz>
 */
class Media implements IExtra
{
	/** @var string Relative path to www root from directory with composer.json */
	private $wwwRoot;
	/** @var string Path to media directory from URL */
	private $basePath;

	private $rules = "";
	private $links = array();
	const
		WEB_CONFIG_START_TAG = "<!-- DynamicMediaDirectories -->",
		WEB_CONFIG_END_TAG = "<!-- DynamicMediaDirectoriesEnd -->";


	/**
	 * Prepare rules and sym links address. After all packages is ran is required to cal method updateWebConfig() or createSymlinks()
	 * @param PackageInterface $package
	 * @param bool $isMain
	 * @return string|void
	 */
	function run(PackageInterface $package, $isMain = true)
	{
		$extra = $package->getExtra();
		if (!isset($extra['media']) OR !isset($extra['media']['directories']) OR !is_array($extra['media']['directories'])) return;

		$this->initPaths($extra['media'], $isMain);
		$vendorName = trim("vendor/" . $package->getName(), '\\/');

		foreach ($extra['media']['directories'] as $name => $path) {
			if (!preg_match("/^[a-zA-Z0-9_-]+$/", $name)) throw new \OutOfRangeException("Name must be corresponding to expression: a-zA-Z0-9_-");
			//generate create config
			$relativePath = "/" . ($isMain ? "" : $vendorName . '/') . trim($path, '\\/');
			$absolutePath = $this->normalize(getcwd() . $relativePath);

			$absoluteMediaPath = $this->normalize(getcwd() . '/' . trim($this->wwwRoot . '/' . $this->basePath, "\\/"));

			if (!is_dir($absoluteMediaPath)) throw new \OutOfRangeException("Media directory does not exist for expected path: $absoluteMediaPath");
			if (!is_dir($absolutePath)) throw new \OutOfRangeException("Directory declared by relative path: '$path' does not exist on absolute path $absolutePath");
			$this->addRule($relativePath, $name);

			//create symlink for unix
			$module = $absoluteMediaPath . '/' . $name;
			if (!is_dir($module) AND !is_link($module)) {
				$this->addLink($absolutePath, $module);
			}
		}
	}

	/**
	 * Register redirection rule
	 * @param $relativePath
	 * @param $name
	 */
	private function addRule($relativePath, $name)
	{
		$this->rules .= "\n\t\t\t<rule name=\"Rewrite media modules to $relativePath/ folder\" stopProcessing=\"true\">"
			. "\n\t\t\t\t<match url=\"^" . $this->basePath . "/$name/(.+)\" ignoreCase=\"false\" />"
			. "\n\t\t\t\t<action type=\"Rewrite\" url=\"$relativePath/{R:1}\" />"
			. "\n\t\t\t</rule>";
	}

	/**
	 * Register path for symlink
	 * @param $absolutePath
	 * @param $module
	 */
	private function addLink($absolutePath, $module)
	{
		$this->links[$absolutePath] = $module;
	}

	/**
	 * Apply redirection rules for IIS to file web.config
	 * @param string $relativePath
	 */
	public function updateWebConfig($relativePath = "web.config")
	{
		$webConfigPath = getcwd() . '/' . trim($relativePath, "\\/");
		if (!is_file($webConfigPath)) {
			echo "IIS $relativePath file not found on path: $webConfigPath";
			return;
		}

		$content = file_get_contents($webConfigPath);
		$tagStart = strpos($content, self::WEB_CONFIG_START_TAG) + strlen(self::WEB_CONFIG_START_TAG);
		$tagEnd = strpos($content, self::WEB_CONFIG_END_TAG);
		$newContent = substr($content, 0, $tagStart)
			. $this->rules
			. "\n\t\t\t" . substr($content, $tagEnd, strlen($content) - $tagEnd);
		file_put_contents($webConfigPath, $newContent);
		echo "Updated web config file: $webConfigPath\n";
	}

	/**
	 * Create symlinks from public to private directories
	 */
	public function createSymlinks()
	{
		foreach ($this->links as $absolutePath => $module) {
			if (!@symlink($absolutePath, $module)) echo "Symlink was not created. Run command again with privileges of administrator\n";
			$htaccess = $absolutePath . "/.htaccess";
			if (!is_file($htaccess)) file_put_contents($htaccess, "Order Allow,Deny\nAllow from all");
		}
	}

	/**
	 * Initialize paths from main package extra parameters
	 * @param $media
	 * @param $isMain
	 */
	private function initPaths($media, $isMain)
	{
		if ($this->wwwRoot AND $this->basePath AND $isMain) throw new \DomainException("Can not run for package marked as main twice.");
		if ($this->wwwRoot AND $this->basePath) return;
		if (!$isMain) throw new \DomainException("Call at first for main package");
		$this->wwwRoot = (isset($media['www-root']) AND $isMain) ? trim($media['www-root'], "\\/") : 'www';
		$this->basePath = (isset($media['base-path']) AND $isMain) ? trim($media['base-path'], "\\/") : 'media';
	}

	/**
	 * Normalize path
	 * @param $path
	 * @return string
	 */
	private function normalize($path)
	{
		$path = str_replace('\\', '/', $path);
		$exp = explode('/', $path);
		$valid = array();
		foreach ($exp as $i => $val) {
			if ($val === "..") {
				array_pop($valid);
			} else if ($val !== ".") {
				array_push($valid, $val);
			}
		}
		return rtrim(implode('/', $valid), '/');

	}
}