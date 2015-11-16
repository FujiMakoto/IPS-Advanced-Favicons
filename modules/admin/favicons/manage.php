<?php


namespace IPS\favicons\modules\admin\favicons;

use IPS\favicons\Favicon;
use IPS\Helpers\Form;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * manage
 */
class _manage extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'manage_manage' );
		parent::execute();
	}
	
	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{		
		/* Create the table */
		$table = new \IPS\Helpers\Table\Db( 'favicons_favicons', \IPS\Http\Url::internal( 'app=favicons&module=favicons&controller=manage' ) );
		$table->langPrefix = 'favicons_';

		/* Columns we need */
		$table->mainColumn = 'name';

		/* Default sort options */
		$table->sortBy = $table->sortBy ?: 'name';
		$table->sortDirection = $table->sortDirection ?: 'asc';

		/* Filters */
		$table->filters = array(
				'favicons_filter_android'   => 'type=' . \IPS\favicons\Favicon::ANDROID,
				'favicons_filter_ios'       => 'type=' . \IPS\favicons\Favicon::IOS,
				'favicons_filter_safari'    => 'type=' . \IPS\favicons\Favicon::SAFARI,
				'favicons_filter_windows'   => 'type=' . \IPS\favicons\Favicon::WINDOWS,
		);

		/* Specify the buttons */
		$rootButtons = array();
		if ( \IPS\Member::loggedIn()->hasAcpRestriction( 'favicons', 'favicons', 'favicons_create' ) )
		{
			$rootButtons[ 'wizard' ] = array(
					'icon'  => 'magic',
					'title' => 'favicons_wizard_run',
					'link'  => \IPS\Http\Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard' ),
					'data'  => array(
							'ipsDialog'                 => '',
							'ipsDialog-title'           => \IPS\Member::loggedIn()->language()->addToStack(
									'favicons_wizard_title'
							),
							//'ipsDialog-fixed'           => '',
							//'ipsDialog-size'            => 'fullscreen',
							'ipsDialog-remoteSubmit'    => 'true'
					)
			);
		}
		$table->rootButtons = $rootButtons;

		/* Display */
		\IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
	}

	/**
	 * Wizard
	 */
	public function wizard()
	{
		$initialData = [];
		try
		{
			$initialData['favicons_master'] = Favicon::master();
		}
		catch ( \UnderflowException $e ) {}

		/* Build wizard */
		$wizard = new \IPS\Helpers\Wizard( array(
				'favicons_master'   => array( $this, '_stepMaster' ),
				'favicons_android'  => array( $this, '_stepAndroid' ),
				'favicons_ios'      => array( $this, '_stepIOS' ),
				'favicons_safari'   => array( $this, '_stepSafari' ),
				'favicons_windows'  => array( $this, '_stepWindows' ),
				'favicons_review'   => array( $this, '_stepRview' ),
		), \IPS\Http\Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard' ), TRUE, $initialData );

		/**
		 * Output
		 */
//		if ( \IPS\Request::i()->isAjax() and \IPS\Request::i()->ajaxValidate )
//		{
//			\IPS\Output::i()->json( array( 'validate' => true ) );
//			return;
//		}

		\IPS\Output::i()->output = $wizard;
	}

	/**
	 * Wizard step: Upload a master / base image to use as the favicon
	 *
	 * @param	array	$data	The current wizard data
	 * @return	string|array
	 */
	public function _stepMaster( $data )
	{
		$form = new Form( 'master', 'continue', \IPS\Http\Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_master' ) );
		//$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_vertical';

		$default = ( !empty($data['favicons_master']) ) ? $data['favicons_master'] : NULL;
		$form->add( new Form\Upload( 'favicons_master', $default, TRUE,
				[
						'storageExtension' => 'favicons_Favicons',
						'image'            => TRUE,
						'obscure'          => FALSE
				]
		) );

		if ( $values = $form->values() )
		{
			if ( !isset( \IPS\Request::i()->ajaxValidate ) )
			{
				$ext = pathinfo( $values['favicons_master']->filename, \PATHINFO_EXTENSION );
				$contents = $values['favicons_master']->contents();
				$values['favicons_master']->delete();

				Favicon::reset();

				$master = \IPS\File::create( 'favicons_Favicons', 'master.' . $ext, $contents, 'favicons', FALSE, NULL, FALSE );
				$master->save();
				$values['favicons_master'] = $master;

				$favicon = new Favicon();
				$favicon->type = Favicon::MASTER;
				$favicon->name = $master->filename;
				$favicon->file = $master;
				$favicon->save();
			}

			return $values;
		}

		return \IPS\Theme::i()->getTemplate( 'wizard' )->step1( $form );
	}

	/**
	 * Wizard step: Android images
	 *
	 * @param	array	$data	The current wizard data
	 * @return	string|array
	 */
	public function _stepAndroid( $data )
	{
		$form = new Form( 'android', 'continue', \IPS\Http\Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_android' ) );
		//$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_vertical';

		$s = \IPS\Settings::i();
		$form->add( new Form\Text( 'favicons_androidAppName', $s->board_name, TRUE ) );
		// $form->add( new Form\Text( 'favicons_androidAppShortName', NULL, FALSE ) );  // Keeping the wizard simple
		$form->add( new Form\Color( 'favicons_androidColor', '3C6994' ) );

		# Browser mode
		$form->add( new Form\YesNo( 'favicons_androidStandalone', NULL, FALSE,
				[
						'togglesOn' => [
								'android_favicons_androidStandalone_startUrl',
								'android_favicons_androidStandalone_orientation'
						]
				]
		) );
		$form->add( new Form\Url( 'favicons_androidStandalone_startUrl', NULL ) );
		$form->add( new Form\Select( 'favicons_androidStandalone_orientation', 'default', FALSE,
				[
						'options' => [
								'default'   => 'favicons_androidStandalone_orientation_default',
								'portrait'  => 'favicons_androidStandalone_orientation_portrait',
								'landscape' => 'favicons_androidStandalone_orientation_landscape'
						]
				]
		) );

		if ( $values = $form->values() )
		{
			if ( !isset( \IPS\Request::i()->ajaxValidate ) )
			{
				$form->saveAsSettings( $values );
				Favicon::generateIcons( Favicon::ANDROID );

				/* Create the manifest.json file */
				\IPS\File::create( 'favicons_Favicons', 'manifest.json', Favicon::androidManifest(), 'favicons', FALSE, NULL, FALSE );
			}

			return $values;
		}

		return \IPS\Theme::i()->getTemplate( 'wizard' )->step2( $form );
	}

	/**
	 * Wizard step: iOS images
	 *
	 * @param	array	$data	The current wizard data
	 * @return	string|array
	 */
	public function _stepIOS( $data )
	{
		$form = new Form( 'ios', 'continue', \IPS\Http\Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_ios' ) );
		//$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_vertical';

		$form->add( new Form\YesNo( 'favicons_iosFancy', TRUE ) );

		if ( $values = $form->values() )
		{
			if ( !isset( \IPS\Request::i()->ajaxValidate ) )
			{
				Favicon::generateIcons( Favicon::IOS );
			}

			return $values;
		}

		return \IPS\Theme::i()->getTemplate( 'wizard' )->step3( $form );
	}

	/**
	 * Wizard step: iOS images
	 *
	 * @param	array	$data	The current wizard data
	 * @return	string|array
	 */
	public function _stepSafari( $data )
	{
		$form = new Form( 'safari', 'continue', \IPS\Http\Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_safari' ) );
		//$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_vertical';

		$form->add( new Form\Upload( 'favicons_safariSvg', NULL, FALSE,
				[
						'storageExtension' => 'favicons_Favicons',
						'allowedFileTypes' => ['svg']
				]
		) );

		if ( $values = $form->values() )
		{
			if ( !isset( \IPS\Request::i()->ajaxValidate ) )
			{
				if ( $values['favicons_safariSvg'] )
				{
					$contents = $values['favicons_safariSvg']->contents();
					$values['favicons_safariSvg']->delete();
					$file = \IPS\File::create( 'favicons_Favicons', 'safari-pinned-tab.svg', $contents, 'favicons',
							FALSE, NULL, FALSE
					);

					$favicon = new Favicon();
					$favicon->type = Favicon::SAFARI;
					$favicon->name = $file->filename;
					$favicon->file = $file;
					$favicon->save();
				}
			}

			return $values;
		}

		return \IPS\Theme::i()->getTemplate( 'wizard' )->step3( $form );
	}

	/**
	 * Wizard step: Safari images
	 *
	 * @param	array	$data	The current wizard data
	 * @return	string|array
	 */
	public function _stepWindows( $data )
	{
		$form = new Form( 'windows', 'continue', \IPS\Http\Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_windows' ) );
		$form->class = 'ipsForm_vertical';

		# Tile color
		$form->add( new Form\Select( 'favicons_msTileColor', '#2d89ef', FALSE,
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
						'unlimitedToggles'  => ['windows_favicons_msTileColor_custom']
				]
		) );
		$form->add( new Form\Color( 'favicons_msTileColor_custom' ) );

		return \IPS\Theme::i()->getTemplate( 'wizard' )->step4( $form );
	}
}