<?php


namespace Pipas\Modules\Composer;

use Composer\Script\Event;
use Pipas\Modules\Composer\Extra\Bower;
use Pipas\Modules\Composer\Extra\Grunt;
use Pipas\Modules\Composer\Extra\Media;

/**
 * Calls extra events for all local packages
 * @author Petr Štipek <p.stipek@email.cz>
 */
class EventReceiver
{
	private static $instance;
	/** @var Bower */
	private $bower;

	/** @var Grunt */
	private $grunt;

	/** @var Media */
	private $mediaDirectory;

	/**
	 * @return Bower
	 */
	public function getBower()
	{
		if (!$this->bower) {
			$this->bower = new Bower();
		}
		return $this->bower;
	}

	/**
	 * @return Grunt
	 */
	public function getGrunt()
	{
		if (!$this->grunt) {
			$this->grunt = new Grunt();
		}
		return $this->grunt;
	}

	/**
	 * @return Media
	 */
	public function getMediaDirectory()
	{
		if (!$this->mediaDirectory) {
			$this->mediaDirectory = new Media();
		}
		return $this->mediaDirectory;
	}

	/**
	 * Calls all extras
	 * @param Event $event
	 */
	public function runEvent(Event $event)
	{
		$composer = $event->getComposer();

		$this->getBower()->run($composer->getPackage());
		$this->getGrunt()->run($composer->getPackage());
		$this->getMediaDirectory()->run($composer->getPackage());
		foreach ($composer->getRepositoryManager()->getLocalRepository()->getPackages() as $package) {
			$this->getBower()->run($package, false);
			$this->getGrunt()->run($package, false);
			$this->getMediaDirectory()->run($package, false);
		}
		$this->getMediaDirectory()->createSymlinks();
		$this->getMediaDirectory()->updateWebConfig();
	}

	protected function __construct()
	{

	}

	public static function run(Event $event)
	{
		if (!self::$instance) {
			self::$instance = new self();
		}
		self::$instance->runEvent($event);
	}

}