<?php
namespace GDO\Maintenance\Method;

use GDO\Core\Method;
use GDO\Date\Time;
use GDO\Maintenance\Module_Maintenance;
use GDO\UI\GDT_Headline;
use GDO\UI\GDT_Page;
use GDO\User\GDO_User;
use GDO\Core\Application;
use GDO\UI\GDT_Redirect;

/**
 * Show the site is in maintenance mode and when it might it.
 *
 * @author gizmore
 * @version 7.0.1
 */
final class ShowMaintenance extends Method
{

	public function isIndexed(): bool
	{
		return false;
	}

	public function isSavingLastUrl(): bool
	{
		return false;
	}

	public function isShownInSitemap(): bool
	{
		return false;
	}

	public function execute()
	{
		$mod = Module_Maintenance::instance();
		if ($end = $mod->cfgEnd())
		{
			$in = $end->getTimestamp() - Application::$TIME;
			$ends = Time::humanDuration($in);
		}
		return (($end) && ($in > 0)) ? $this->error('err_maintenance_mode', [
			sitename(),
			$ends
		]) : $this->error('err_maintenance_mode_unknown', [
			sitename()
		]);
	}

	# ## API ##
	public static function go(): void
	{
		global $me;
		if ( !self::isCurrentMethodWhitelisted())
		{
			$user = GDO_User::current();
			if (( !$user->isStaff()) && ( !$user->isSystem()))
			{
				GDO_User::setCurrent(GDO_User::ghost());
				$me = self::make();
// 				GDT_Redirect::to($me->href());
// 				die(0);
			}
			if ($me)
			{
				GDT_Page::instance()->topResponse()->addFields(
					GDT_Headline::make()->level(1)
						->text('msg_maintenance_mode'));
			}
		}
	}

	# ################
	# ## Whitelist ###
	# ################
	/**
	 * Allow a few functions to operate normally on normal users.
	 *
	 * @return string[]
	 */
	public static function getWhitelist()
	{
		$default = strtolower(GDO_MODULE . '.' . GDO_METHOD);
		return [
			$default,
			'core.fileserver',
			'login.form',
			'language.gettrans',
			'captcha.image',
			'maintenance.show',
		];
	}

	public static function isCurrentMethodWhitelisted(): bool
	{
		/** @var $me \GDO\Core\Method **/
		global $me;
		$module = strtolower($me->getModuleName());
		$method = strtolower($me->getMethodName());
		return in_array("{$module}.{$method}", self::getWhitelist(), true);
	}

}
