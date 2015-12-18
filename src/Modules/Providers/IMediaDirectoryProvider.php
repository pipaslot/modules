<?php

namespace Pipas\Modules\Providers;

use Pipas\Modules\Configurators\IMediaDirectoryConfig;

/**
 * Provide access to module medial folder with front-end scripts located into root folder of application named 'media'
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface IMediaDirectoryProvider
{
	/**
	 * @param IMediaDirectoryConfig $config
	 */
	function setupMediaDirectory(IMediaDirectoryConfig $config);
}