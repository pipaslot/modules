<?php


namespace App;


use Nette\Application\UI\Form;
use Pipas\Modules\Presenters\AjaxPresenter;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class MiddleLayoutPresenter extends AjaxPresenter
{
	protected function createComponentTestForm()
	{
		$form = new Form();
		$form->addText("input", "Input");
		$form->addSubmit("submit", "Save")
			->onClick[] = function () {
			$this->flashMessage("Saved", "success");
		};
		$form->addSubmit("sendAndClose", "Save and close")
			->onClick[] = function () {
			$this->flashMessage("Saved", "success");
			$this->modal->close();
		};

		return $form;
	}
}