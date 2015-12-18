<?php

namespace Pipas\Modules\Providers;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface IDoctrineProvider
{
	/**
	 * @param Configuration $config
	 * @param EntityManager $entityManager
	 * @param EventManager $eventManager
	 */
	function setupDoctrine(Configuration $config, EntityManager $entityManager, EventManager $eventManager);
}