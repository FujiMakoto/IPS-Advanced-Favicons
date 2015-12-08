<?php
/**
 * @brief		Advanced Favicons Application Class
 * @author		<a href='https://www.Makoto.io'>Makoto Fujimoto</a>
 * @copyright	(c) 2015 Makoto Fujimoto
 * @package		IPS Social Suite
 * @subpackage	Advanced Favicons
 * @since		13 Nov 2015
 * @version		
 */
 
namespace IPS\favicons;

/**
 * Advanced Favicons Application Class
 */
class _Application extends \IPS\Application
{
	/**
	 * Application icon
	 *
	 * @return  string
	 */
	public function get__icon()
	{
		/* "file" is meant to represent the generic page icon displayed when a website doesn't have a favicon */
		return 'file';
	}

	/**
	 * Extract developer resources on installation
	 */
	public function installOther()
	{
		try
		{
			\IPS\favicons\DevFiles::extract();
		}
		catch ( \Exception $e ) {}
	}
}