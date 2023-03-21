<?php
namespace GDO\Maintenance\Method;

use GDO\Core\Application;
use GDO\Core\Method;
use GDO\Date\Time;
use GDO\Maintenance\Module_Maintenance;
use GDO\UI\GDT_Redirect;
use GDO\User\GDO_User;
use const true;

/**
 * Show the site is in maintenance mode and when it might it.
 *
 * @version 7.0.1
 * @author gizmore
 */
final class ShowMaintenance extends Method
{

	public static function go(): void
	{
		global $me;
		if (!self::isCurrentMethodWhitelisted())
		{
			$user = GDO_User::current();
			if ((!$user->isStaff()) && (!$user->isSystem()))
			{
				GDO_User::setCurrent(GDO_User::ghost());
				$me = self::make();
				GDT_Redirect::to($me->href());
// 				die(0);
			}
// 			if ($me)
// 			{
// 				GDT_Page::instance()->topResponse()->addFields(
// 					GDT_Headline::make()->level(1)
// 						->text('msg_maintenance_mode'));
// 			}
		}
	}

	public static function isCurrentMethodWhitelisted(): bool
	{
		/** @var $me Method * */
		global $me;
		$module = strtolower($me->getModuleName());
		$method = strtolower($me->getMethodName());
		return in_array("{$module}.{$method}", self::getWhitelist(), true);
	}

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
			'maintenance.showmaintenance',
		];
	}

	public function isIndexed(): bool
	{
		return false;
	}

	# ## API ##

	public function isSavingLastUrl(): bool
	{
		return false;
	}

	# ################
	# ## Whitelist ###
	# ################

	public function isShownInSitemap(): bool
	{
		return false;
	}

	public function execute()
	{
		$mod = Module_Maintenance::instance();
		if (!$mod->cfgOn())
		{
			# Ended already
			return GDT_Redirect::to(hrefDefault());
		}

		# timed or unknown.
		if ($end = $mod->cfgEnd())
		{
			$in = $end->getTimestamp() - Application::$TIME;
			$ends = Time::humanDuration($in);
		}
		return (($end) && ($in > 0)) ? $this->error('err_maintenance_mode', [
			sitename(),
			$ends,
		]) : $this->error('err_maintenance_mode_unknown', [
			sitename(),
		]);
	}

}
