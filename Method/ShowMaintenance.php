<?php
namespace GDO\Maintenance\Method;

use GDO\Core\Method;
use GDO\Date\Time;
use GDO\Maintenance\Module_Maintenance;
use GDO\Core\Application;

/**
 * @author gizmore
 */
final class ShowMaintenance extends Method
{
    public function isSEOIndexed() { return false; }
    
    public function execute()
    {
        $mod = Module_Maintenance::instance();
        if ($end = $mod->cfgEnd())
        {
            $in = $end - Application::$TIME;
            $ends = Time::humanDuration($in);
        }
        return ($end) && ($in > 0) ? 
            $this->error('err_maintenance_mode', [sitename(), $ends]) :
            $this->error('err_maintenance_mode_unknown', [sitename()]);
    }
    
}
