<?php
namespace GDO\Maintenance;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\Date\GDT_DateTime;
use GDO\Maintenance\Method\ShowMaintenance;

/**
 * Maintenance module.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 6.10.1
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
            GDT_DateTime::make('maintenance_end')->format('min'),
        ];
    }
    public function cfgOn() : bool { return $this->getConfigValue('maintenance_on'); }
    public function cfgEnd() { return $this->getConfigValue('maintenance_end'); }
    
    ############
    ### Init ###
    ############
    public function onInit()
    {
        if ($this->cfgOn())
        {
        	ShowMaintenance::go();
        }
    }
    
}
