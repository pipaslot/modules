<?php

namespace Pipas\Modules\Providers;
use Pipas\Modules\Configurators\IPresenterMappingConfig;

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