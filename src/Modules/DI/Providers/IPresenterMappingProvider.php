<?php

namespace Pipas\Modules\DI\Providers;
use Pipas\Modules\DI\Providers\Configurators\IPresenterMappingConfig;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface IPresenterMappingProvider
{
	/**
	 * Enable add ownPresenter class mapping
	 *
	 * @param IPresenterMappingConfig &$presenterMappingConfig
	 *
	 * @return void
	 */
	public function setupPresenterMapping(IPresenterMappingConfig &$presenterMappingConfig);
}