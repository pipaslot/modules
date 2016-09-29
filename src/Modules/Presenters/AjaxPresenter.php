<?php
namespace Pipas\Modules\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Security\IAuthorizator;
use Pipas\Modules\Presenters\Modal\ModalDialog;
use Pipas\Modules\Results\Message;
use Pipas\Modules\Results\UIResult;
use Pipas\Modules\Templates\LayoutProvider;

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

	/** @var callable[]  function (AjaxPresenter $presenter); */
	public $onAccessDenied;

	/** @var callable[]  function (AjaxPresenter $presenter, $resource, $permission); */
	public $onPermissionDenied;

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
			} else {
				throw $e;
			}
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

	/**
	 * Presents UI result messages as presenter flash message
	 * @param UIResult $result
	 */
	protected function processUiResult(UIResult $result)
	{
		$levelToType = array(
			Message::LEVEL_SUCCESS => 'success',
			Message::LEVEL_INFO => 'info',
			Message::LEVEL_WARNING => 'warning',
			Message::LEVEL_ERROR => 'error'
		);
		foreach ($result->getMessages() as $message) {
			$type = $levelToType[$message->getLevel()];
			parent::flashMessage(($message->getRate() > 1 ? $message->getRate() . 'x: ' : '') . $message->getText(), $type);
		}
	}

	/********************************** Security helpers **********************************/
	/**
	 * Verify if user is logged in, if not onAccessDenied event is invoked and error is thrown
	 * @return void
	 */
	protected function requireLoggedUser()
	{
		if (!$this->user->isLoggedIn()) {
			$this->onAccessDenied($this);
			$this->error("Access enabled only for logged users.", IResponse::S401_UNAUTHORIZED);
		}
	}

	/**
	 * Verify if user has permission, if not onPermissionDenied event is invoked and error is thrown
	 * @param null $resource
	 * @param null $privilege
	 * @return void
	 */
	protected function requirePermission($resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL)
	{
		if (!$this->user->isAllowed($resource, $privilege)) {
			$this->onPermissionDenied($this, $resource, $privilege);
			$this->error("Access enabled only for logged users.", IResponse::S403_FORBIDDEN);
		}
	}
}
