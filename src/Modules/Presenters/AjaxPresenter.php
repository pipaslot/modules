<?php
namespace Pipas\Modules\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;
use Pipas\Modules\Templates\LayoutProvider;
use Pipas\Modules\Templates\TemplateExtraParameters;

/**
 * Providing AJAX operations and default snippet invalidation.
 * - title - Defined variable $title and snippet "title"
 * - content - Defined block #content and snippet "content"
 * - styles     - Defined block #styles and snippet "styles" placed into HTML body
 * - scripts - Defined block #scripts and snippet "scripts" placed into HTML body
 * @author Petr Štipek <p.stipek@email.cz>
 */
abstract class AjaxPresenter extends Presenter
{
	/** @var LayoutProvider */
	private $layoutProvider;

	/** @var TemplateExtraParameters */
	private $templateParameters;

	/**
	 * @param LayoutProvider $layoutProvider
	 */
	public function injectLayoutProvider(LayoutProvider $layoutProvider)
	{
		$this->layoutProvider = $layoutProvider;
	}

	/**
	 * @param TemplateExtraParameters $templateParameters
	 */
	public function injectTemplateExtraParameters(TemplateExtraParameters $templateParameters)
	{
		$this->templateParameters = $templateParameters;
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->ajaxLayoutPath = __DIR__ . "/../Templates/@layout.latte";
	}

	protected function afterRender()
	{
		parent::afterRender();
		//If is ajax mode and all components are valid, invalidate default pro prevent sending pure html instead of JSON response
		if ($this->isAjax() AND !$this->isControlInvalid() AND $this->layout != false) {
			$this->redrawControl("title");
			$this->redrawControl("content");
			$this->redrawControl("styles");
			$this->redrawControl("scripts");
		}
	}

	/**
	 * Invalidation
	 * @throws BadRequestException
	 */
	public function processSignal()
	{
		$signal = $this->getSignal();

		// If is not exist signal or is not Ajax
		if ((!$signal OR empty($signal[0]) OR !$this->isAjax()) AND $this->layout != false) {
			$this->redrawControl("title");
			$this->redrawControl("content");
			$this->redrawControl("styles");
			$this->redrawControl("scripts");
		}
		try {
			parent::processSignal();
		} catch (BadRequestException $e) {
			if ($this->isAjax()) {
				$this->flashMessage($e->getMessage(), 'error');
				$this->sendPayload();
			} else
				throw $e;
		}
	}

	/**
	 * @return ITemplate
	 */
	protected function createTemplate()
	{
		$template = parent::createTemplate();
		$template->bowerPath = $this->templateParameters->getBowerPath($template->basePath);
		$template->mediaPath = $this->templateParameters->getMediaPath($template->basePath);
		$template->modulePath = $this->templateParameters->getModulePath($template->basePath);
		return $template;
	}


	/**
	 * Search default layouts of AjaxPresenter
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		return $this->layoutProvider->prepareLayouts(parent::formatLayoutTemplateFiles(), $this->name);
	}
}
