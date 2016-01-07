<?php


namespace Pipas\Modules\Composer;

use Composer\Script\Event;
use Pipas\Modules\Composer\Extra\Bower;
use Pipas\Modules\Composer\Extra\Grunt;
use Pipas\Modules\Composer\Extra\MediaDirectory;

/**
 * Calls extra events for all local packages
 * @author Petr Štipek <p.stipek@email.cz>
 */
class EventReceiver
{
	/** @var Bower */
	private $bower;

	/** @var Grunt */
	private $grunt;

	/** @var MediaDirectory */
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
	 * @return MediaDirectory
	 */
	public function getMediaDirectory()
	{
		if (!$this->mediaDirectory) {
			$this->mediaDirectory = new MediaDirectory();
		}
		return $this->mediaDirectory;
	}

	/**
	 * Calls all extras
	 * @param Event $event
	 */
	public function run(Event $event)
	{
		$composer = $event->getComposer();

		$this->getBower()->run($composer->getPackage(), true);
		$this->getGrunt()->run($composer->getPackage(), true);
		$this->getMediaDirectory()->run($composer->getPackage(), true);
		foreach ($composer->getRepositoryManager()->getLocalRepository()->getPackages() as $package) {
			$this->getBower()->run($package);
			$this->getGrunt()->run($package);
			$this->getMediaDirectory()->run($package);
		}
		$this->getMediaDirectory()->createSymlinks();
		$this->getMediaDirectory()->updateWebConfig();
	}
}