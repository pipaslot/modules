<?php
namespace Pipas\Modules\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;
use Pipas\Modules\Presenters\Modal\ModalDialog;
use Pipas\Modules\Templates\LayoutProvider;
use Pipas\Modules\Templates\TemplateExtraParameters;

/**
 * Providing AJAX operations and default snippet invalidation.
 * - title - Defined variable $title and snippet "title"
 * - content - Defined block #content and snippet "content"
 * - styles     - Defined block #styles and snippet "styles" placed into HTML body
 * - scripts - Defined block #scripts and snippet "scripts" placed into HTML body
 * @author Petr Štipek <p.stipek@email.cz>
 * @property ModalDialog $modal
 */
abstract class AjaxPresenter extends Presenter
{
	/** @var ModalDialog */
	private $modal;
	
	/** @var LayoutProvider */
	private $layoutProvider;

	/**
	 * Modal dialog control
	 * @return ModalDialog
	 */
	public function getModal()
	{
		if (!$this->modal) {
			$this->modal = new ModalDialog($this);
		}
		return $this->modal;
	}

	/**
	 * @param LayoutProvider $layoutProvider
	 * @internal
	 */
	public function injectLayoutProvider(LayoutProvider $layoutProvider)
	{
		$this->layoutProvider = $layoutProvider;
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->ajaxLayoutPath = __DIR__ . "/../Templates/@" . ($this->getModal()->isRequested() ? "modal" : "layout") . ".latte";
		$this->template->isDebugMode = $this->context->parameters['debugMode'];
	}

	protected function afterRender()
	{
		parent::afterRender();

		if ($this->getModal()->isRequested() AND !$this->isControlInvalid()) {
			$this->redrawControl("modalTitle");
			$this->redrawControl("modalContent");
		} //If is ajax mode and all components are valid, invalidate default pro prevent sending pure html instead of JSON response
		else if ($this->isAjax() AND !$this->isControlInvalid() AND $this->layout !== false) {
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

		// If does not exist signal or is not Ajax request ,then redraw snippets
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
	 * Search default layouts of AjaxPresenter
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		$mode = $this->getModal()->isRequested() ? LayoutProvider::MODE_MODAL : LayoutProvider::MODE_DOCUMENT;
		return $this->layoutProvider->prepareLayouts(parent::formatLayoutTemplateFiles(), $this->name, $mode);
	}
}
