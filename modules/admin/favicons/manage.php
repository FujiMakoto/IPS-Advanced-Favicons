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
				'favicons_windows'  => array( $this, '_stepWindows' )
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
		$form->add( new Form\Color( 'favicons_androidColor', 'FFFFFF' ) );

		# Browser mode
		$form->add( new Form\YesNo( 'favicons_androidStandalone', $s->favicons_androidStandalone, FALSE,
				[
						'togglesOn' => [
								'android_favicons_androidStandalone_startUrl',
								'android_favicons_androidStandalone_orientation'
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

		if ( $values = $form->values() )
		{
			if ( !isset( \IPS\Request::i()->ajaxValidate ) )
			{
				$form->saveAsSettings( $values );
				Favicon::generateForAndroid();
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
		$form = new Form( 'ios', 'continue' );
		$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_vertical';

		$form->add( new Form\Text( 'foo' ) );

		return \IPS\Theme::i()->getTemplate( 'wizard' )->step3( $form );
	}

	/**
	 * Wizard step: Safari images
	 *
	 * @param	array	$data	The current wizard data
	 * @return	string|array
	 */
	public function _stepSafari( $data )
	{
		$form = new Form( 'android', 'continue' );
		$form->class = 'ipsForm_vertical';

		$form->add( new Form\Text( 'foo' ) );

		return \IPS\Theme::i()->getTemplate( 'wizard' )->step4( $form );
	}

	/**
	 * Wizard step: Safari images
	 *
	 * @param	array	$data	The current wizard data
	 * @return	string|array
	 */
	public function _stepWindows( $data )
	{
		$form = new Form( 'android', 'continue' );
		$form->class = 'ipsForm_vertical';

		$form->add( new Form\Text( 'foo' ) );

		return \IPS\Theme::i()->getTemplate( 'wizard' )->step5( $form );
	}
}