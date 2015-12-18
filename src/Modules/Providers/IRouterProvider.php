<?php

namespace Pipas\Modules\Providers;

use Nette\Application\Routers\RouteList;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface IRouterProvider
{
	/**
	 * Returns array of ServiceDefinition, that will be appended to setup of router service
	 *
	 * @return RouteList
	 */
	public function getRouteList();
}