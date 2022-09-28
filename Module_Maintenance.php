<?php
namespace GDO\Maintenance;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\User\GDO_User;
use GDO\Login\Method\Logout;
use GDO\Core\GDT_Response;
use GDO\Core\Website;
use GDO\Date\GDT_DateTime;
use GDO\UI\GDT_Page;
use GDO\UI\GDT_Headline;

/**
 * Maintenance module.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 6.10.1
 */
final class Module_Maintenance extends GDO_Module
{
    public $module_priority = 7; # kill user early.
    
    public function onLoadLanguage() : void
    {
        $this->loadLanguage('lang/maintenance');
    }
    
    ##############
    ### Config ###
    ##############
    public function getConfig() : array
    {
        return [
            GDT_Checkbox::make('maintenance_on')->initial('0'),
            GDT_DateTime::make('maintenance_end')->format('min'),
        ];
    }
    public function cfgOn() : bool { return $this->getConfigValue('maintenance_on'); }
    public function cfgEnd() { return $this->getConfigValue('maintenance_end'); }
    
    #################
    ### Whitelist ###
    #################
    /**
     * Allow a few functions to operate normally on normal users.
     * @return string[]
     */
    public static function getWhitelist()
    {
        return [
            'login.form',
            'language.gettrans',
            'captcha.image',
            'maintenance.showmaintenance',
        ];
    }
    
    public function isCurrentMethodWhitelisted()
    {
    	/** @var $me \GDO\Core\Method **/
    	global $me;
        $mo = strtolower($me->getModuleName());
        $me = strtolower($me->getMethodName());
        return in_array("{$mo}.{$me}", $this->getWhitelist(), true);
    }
    
    ############
    ### Init ###
    ############
    public function onInit()
    {
        if ($this->cfgOn())
        {
            if ($this->isCurrentMethodWhitelisted())
            {
                return;
            }
            
            if ( (!GDO_User::current()->isStaff()) &&
                 (!GDO_User::current()->isSystem()) )
            {
                $response = GDT_Response::make();
                if (module_enabled('Login'))
                {
                    $response->addField(Logout::make()->executeWithInit());
                }
                Website::redirect(href('Maintenance', 'ShowMaintenance'));
                GDO_User::setCurrent(GDO_User::ghost());
            }
            elseif (GDO_User::current()->isStaff())
            {
                GDT_Page::instance()->topResponse()->addField(
                    GDT_Headline::make()->level(1)->text('msg_maintenance_mode'));
            }
        }
    }
    
}
