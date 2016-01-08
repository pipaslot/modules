<?php
namespace Pipas\Modules\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;

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
	protected function beforeRender()
	{
		parent::beforeRender();
		//If is ajax mode and all components are valid, invalidate default pro prevent sending pure html instead of JSON response
		if ($this->isAjax() AND !$this->isControlInvalid()) {
			$this->redrawControl("title");
			$this->redrawControl("content");
			$this->redrawControl("styles");
			$this->redrawControl("scripts");
		}
		$this->template->ajaxLayoutPath = __DIR__ . "/templates/@layout.latte";
	}

	/**
	 * Invalidation
	 * @throws BadRequestException
	 */
	public function processSignal()
	{
		$signal = $this->getSignal();

		// If is not exist signal or is not Ajax
		if (!$signal OR empty($signal[0]) OR !$this->isAjax()) {
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
	 * Search default layout ofAjaxPresenter
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		$list = parent::formatLayoutTemplateFiles();
		$list[] = __DIR__ . "/templates/@layout.latte";
		return $list;
	}
}
