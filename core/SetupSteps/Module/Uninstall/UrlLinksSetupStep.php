<?php


namespace ImpressCMS\Core\SetupSteps\Module\Uninstall;


use icms;
use icms_module_Object;
use ImpressCMS\Core\SetupSteps\OutputDecorator;
use ImpressCMS\Core\SetupSteps\SetupStepInterface;
use function icms_buildCriteria;

class UrlLinksSetupStep implements SetupStepInterface
{

	/**
	 * @inheritDoc
	 */
	public function execute(icms_module_Object $module, OutputDecorator $output, ...$params): bool
	{
		// delete urllinks
		try {
			$urllink_handler = icms::handler('icms_data_urllink');
			$urllink_handler->deleteAll(icms_buildCriteria(array("mid" => $module->mid)));
		} catch (\Exception $exception) {

		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getPriority(): int
	{
		return 50;
	}
}