<?php
namespace GDO\Maintenance;

use DateTime;
use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\Date\GDT_DateTime;
use GDO\Maintenance\Method\ShowMaintenance;

/**
 * Maintenance module.
 *
 * @version 7.0.1
 * @since 6.10.1
 * @author gizmore
 */
final class Module_Maintenance extends GDO_Module
{

	public $module_priority = 10; # kill user early.

	public function onLoadLanguage(): void
	{
		$this->loadLanguage('lang/maintenance');
	}

	##############
	### Config ###
	##############
	public function getConfig(): array
	{
		return [
			GDT_Checkbox::make('maintenance_on')->initial('0'),
			GDT_DateTime::make('maintenance_end')->format('minute'),
		];
	}

	public function cfgEnd(): ?DateTime { return $this->getConfigValue('maintenance_end'); }

	public function hookBeforeExecute(): void
	{
		if ($this->cfgOn())
		{
			ShowMaintenance::go();
		}
	}

	############
	### Init ###
	############

	public function cfgOn(): bool { return $this->getConfigValue('maintenance_on'); }

}
