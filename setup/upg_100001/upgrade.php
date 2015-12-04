<?php


namespace IPS\favicons\setup\upg_100001;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * 1.0.1 Upgrade Code
 */
class _Upgrade
{
	/**
	 * Update the browserconfig.xml file on update
	 *
	 * @return	array	If returns TRUE, upgrader will proceed to next step. If it returns any other value, it will set this as the value of the 'extra' GET parameter and rerun this step (useful for loops)
	 */
	public function step1()
	{
		try
		{
			\IPS\File::create( 'favicons_Favicons', 'browserconfig.xml', \IPS\favicons\Favicon::microsoftBrowserConfig(), 'favicons', FALSE, NULL, FALSE );
		} catch (\Exception $e) {}
		return TRUE;
	}
	
	// You can create as many additional methods (step2, step3, etc.) as is necessary.
	// Each step will be executed in a new HTTP request
}