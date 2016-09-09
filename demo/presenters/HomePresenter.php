<?php


namespace App;


use Pipas\Modules\Presenters\AjaxPresenter;
use Pipas\Modules\Results\UIResult;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class HomePresenter extends AjaxPresenter
{
	public function actionDefault()
	{
		$result = new UIResult();
		$result->addSuccess("Custom Success");
		$result->addInfo("Custom info", 2);
		$result->addWarning("Custom Warning", 3);
		$result->addError("Custom Error", 4);
		$this->processUiResult($result);
	}
}