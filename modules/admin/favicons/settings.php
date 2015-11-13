<?php


namespace IPS\favicons\modules\admin\favicons;

use IPS\Helpers\Form;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'settings_manage' );
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$s = \IPS\Settings::i();
		$form = new Form( 'settings' );

		/**
		 * Android Chrome
		 */
		$form->addTab( 'favicons_androidTab' );
		$form->addHeader( 'favicons_androidHeader' );

		$form->add( new Form\Text( 'favicons_androidAppName', $s->favicons_androidAppName ) );
		$form->add( new Form\Color( 'favicons_androidColor', $s->favicons_androidColor ) );

		# Browser mode
		$form->add( new Form\YesNo( 'favicons_androidStandalone', $s->favicons_androidStandalone, FALSE,
			[
				'togglesOn' => [
					'settings_favicons_androidStandalone_startUrl',
					'settings_favicons_androidStandalone_orientation'
				]
			]
		) );
		$form->add( new Form\Url( 'favicons_androidStandalone_startUrl', $s->favicons_androidStandalone_startUrl ) );
		$form->add( new Form\Select( 'favicons_androidStandalone_orientation', $s->favicons_androidStandalone_orientation, FALSE,
			[
				'options' => [
					'default'   => 'favicons_androidStandalone_orientation_default',
					'portrait'  => 'favicons_androidStandalone_orientation_portrait',
					'landscape' => 'favicons_androidStandalone_orientation_landscape'
				]
			]
		) );

		/**
		 * iOS - Placeholder, currently has not usable settings
		 */
		/*$form->addTab( 'favicons_iosTab' );
		$form->addHeader( 'favicons_iosHeader' );*/

		/**
		 * Microsoft Windows 8 and 10
		 */
		$form->addTab( 'favicons_msTab' );
		$form->addHeader( 'favicons_msHeader' );

		# Tile color
		$form->add( new Form\Select( 'favicons_msTileColor', $s->favicons_msTileColor, FALSE,
			[
				'options' => [
					'#2d89ef'   => 'favicons_msTileColor_blue',
					'#2b5797'   => 'favicons_msTileColor_darkBlue',
					'#00aba9'   => 'favicons_msTileColor_teal',
					'#9f00a7'   => 'favicons_msTileColor_lightPurple',
					'#603cba'   => 'favicons_msTileColor_darkPurple',
					'#b91d47'   => 'favicons_msTileColor_darkRed',
					'#da532c'   => 'favicons_msTileColor_darkOrange',
					'#ffc40d'   => 'favicons_msTileColor_yellow',
					'#00a300'   => 'favicons_msTileColor_green'
				],
				// We don't use userSuppliedInput here because we want to be able to display a Color form, not Text
				'unlimited'         => 'custom',
				'unlimitedLang'     => 'favicons_msTileColor_custom',
				'unlimitedToggles'  => ['settings_favicons_msTileColor_custom']
			]
		) );
		$form->add( new Form\Color( 'favicons_msTileColor_custom', $s->favicons_msTileColor_custom ) );

		/**
		 * Output
		 */
		\IPS\Output::i()->output = $form;
	}
	
	// Create new methods with the same name as the 'do' parameter which should execute it
}