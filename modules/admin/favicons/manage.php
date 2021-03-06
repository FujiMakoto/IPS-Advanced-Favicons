<?php


namespace IPS\favicons\modules\admin\favicons;

use IPS\Db;
use IPS\Dispatcher;
use IPS\favicons\Favicon;
use IPS\Helpers\Form;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Request;
use IPS\Session;
use IPS\Settings;
use IPS\Theme;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Manage favicons
 */
class _manage extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return  void
	 */
	public function execute()
	{
		Dispatcher::i()->checkAcpPermission( 'favicons_manage' );
		parent::execute();
	}

	/**
	 * Display Favicons table
	 *
	 * @return  void
	 */
	protected function manage()
	{
		/* Create the table */
		$table = new \IPS\Helpers\Table\Db(
				'favicons_favicons', Url::internal( 'app=favicons&module=favicons&controller=manage' )
		);
		$table->langPrefix = 'favicons_';

		/* Columns we need */
		$table->include = [
				'file',
				'type',
		];
		$table->mainColumn = 'name';

		/* Default sort options */
		$table->sortBy = $table->sortBy ?: 'type';
		$table->sortDirection = $table->sortDirection ?: 'asc';
		$table->noSort = [ 'file' ];

		/* Filters */
		$table->filters = [
				'favicons_filter_android' => 'type=' . Favicon::ANDROID,
				'favicons_filter_ios'     => 'type=' . Favicon::IOS,
				'favicons_filter_safari'  => 'type=' . Favicon::SAFARI,
				'favicons_filter_windows' => 'type=' . Favicon::WINDOWS,
		];

		/* Custom parsers */
		$self = $this;
		$table->parsers = [
				'file' => function ( $val, $row ) use ( $self )
				{
					$favicon = Favicon::constructFromData( $row );
					$imgUrl = $favicon->getFileUrl();

					return Theme::i()->getTemplate( 'manage' )->faviconPreview( $imgUrl, $favicon->name );
				},
				'type' => function ( $val, $row ) use ( $self )
				{
					$type = Member::loggedIn()->language()->addToStack( "favicons_type_{$row['type']}" );
					$type = $type . " ( {$row['width']} x {$row['height']} )";

					return $type;
				},
				'size' => function ( $val, $row ) use ( $self )
				{
					$favicon = Favicon::constructFromData( $row );

					return $favicon->sizes;
				}
		];

		/* Specify the buttons */
		$rootButtons = [ ];
		if ( Member::loggedIn()->hasAcpRestriction( 'favicons', 'favicons', 'favicons_create' ) )
		{
			$rootButtons['wizard'] = [
					'icon'  => 'magic',
					'title' => 'favicons_wizard_run',
					'link'  => Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_new=1' ),
					// I've wasted so much time trying to get this to work. At this point, I give up. If anyone else
					// wants to take a stab at it, good luck.
					/*'data'  => [
							'ipsDialog'                 => '',
							'ipsDialog-title'           => Member::loggedIn()->language()->addToStack(
									'favicons_wizard_title'
							),
							//'ipsDialog-fixed'           => '',
							//'ipsDialog-size'            => 'fullscreen',
							'ipsDialog-remoteSubmit'    => 'true'
					]*/
			];
		}
		if ( Member::loggedIn()->hasAcpRestriction( 'favicons', 'favicons', 'favicons_manage_settings' ) )
		{
			$class = Settings::i()->favicons_setUpComplete ? '' : 'ipsButton_disabled';
			$rootButtons['settings'] = [
					'icon'  => 'cog',
					'class' => $class,
					'title' => 'favicons_settings',
					'link'  => Url::internal( 'app=favicons&module=favicons&controller=manage&do=settings' ),
					'data'  => [
							'ipsDialog'       => '',
							'ipsDialog-title' => Member::loggedIn()->language()->addToStack(
									'favicons_settings'
							)
					]
			];
		}
		if ( Member::loggedIn()->hasAcpRestriction( 'favicons', 'favicons', 'favicons_create' ) )
		{
			$class = count( Favicon::favicons() ) ? '' : 'ipsButton_disabled ';
			$rootButtons['reset'] = [
					'icon'  => 'trash',
					'class' => $class . 'ipsButton_negative',
					'title' => 'favicons_reset',
					'link'  => Url::internal( 'app=favicons&module=favicons&controller=manage&do=reset' )->csrf(),
					'data'  => [ 'delete' => TRUE ]
			];
		}
		$table->rootButtons = $rootButtons;

		$table->rowButtons = function ( $row ) use ( $self )
		{
			$return = [ ];

			if ( Member::loggedIn()->hasAcpRestriction( 'favicons', 'favicons', 'favicons_create' ) )
			{
				$return['edit'] = [
						'icon'  => 'pencil',
						'title' => 'favicons_edit',
						'link'  => \IPS\Http\Url::internal(
										'app=favicons&module=favicons&controller=manage&do=edit&id='
								) . $row['id'],
						'data'  => [
								'ipsDialog'       => '',
								'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack(
										'favicons_edit'
								)
						]
				];
			}

			return $return;
		};

		/* Display */
		Output::i()->title = Member::loggedIn()->language()->addToStack( 'favicons_manage_title' );
		Output::i()->cssFiles =
				array_merge( Output::i()->cssFiles, Theme::i()->css( 'favicons.css', 'favicons', 'admin' ) );
		Output::i()->output = Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
	}

	/**
	 * Setup Wizard
	 *
	 * @return  void
	 */
	public function wizard()
	{
		Dispatcher::i()->checkAcpPermission( 'favicons_create' );

		$initialData = [ ];
		try
		{
			$initialData['favicons_master'] = Favicon::baseImage();
		}
		catch ( \UnderflowException $e )
		{
		}

		/* Build wizard */
		$wizard = new \IPS\Helpers\Wizard(
				[
						'favicons_master'   => [ $this, '_stepMaster' ],
						'favicons_android'  => [ $this, '_stepAndroid' ],
						'favicons_ios'      => [ $this, '_stepIOS' ],
						'favicons_safari'   => [ $this, '_stepSafari' ],
						'favicons_windows'  => [ $this, '_stepWindows' ],
						'favicons_rewrites' => [ $this, '_stepRewrites' ],
						'favicons_review'   => [ $this, '_stepReview' ],
				], Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard' ), TRUE, $initialData
		);

		/**
		 * Output
		 */
		Output::i()->output = $wizard;
	}

	/**
	 * Get photo for cropping
	 * If the photo is on a different domain to the JS that handles cropping,
	 * it will be blocked because of CORS. See notes in Cropper documentation.
	 *
	 * @return    void
	 */
	protected function cropPhotoGetPhoto()
	{
		Session::i()->csrfCheck();
		$original = Favicon::baseImage();
		$headers = [ 'Content-Disposition' => Output::getContentDisposition( 'inline', $original->filename ) ];
		Output::i()->sendOutput( $original->contents(), 200, \IPS\File::getMimeType( $original->filename ), $headers );
	}

	/**
	 * Wizard step: Upload a master / base image to use as the favicon
	 *
	 * @param   array $data The current wizard data
	 * @return  string|array
	 */
	public function _stepMaster( $data )
	{
		$stepUrl = Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_master' );
		$form = new Form( 'master', 'continue', $stepUrl );
		$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_vertical';

		$default = ( !empty( $data['favicons_master'] ) ) ? $data['favicons_master'] : NULL;
		$form->add(
				new Form\Upload(
						'favicons_master', $default, FALSE, [
								'storageExtension' => 'favicons_Favicons',
								'image'            => TRUE,
								'obscure'          => FALSE
						]
				)
		);

		if ( $values = $form->values() )
		{
			if ( !isset( Request::i()->ajaxValidate ) )
			{
				$image = $values['favicons_master']->contents();
				$values['favicons_master']->delete();

				/* Convert our original image to PNG format */
				if ( pathinfo( $values['favicons_master']->filename, \PATHINFO_EXTENSION ) != 'png' )
				{
					ob_start();
					imagepng( imagecreatefromstring( $image ), NULL );
					$image = ob_get_clean();
				}

				Favicon::reset();

				$master = \IPS\File::create(
						'favicons_Favicons', 'original.png', $image, 'favicons', FALSE, NULL, FALSE
				);
				$master->save();
				$values['favicons_master'] = $master;

				$favicon = new Favicon();
				$favicon->type = Favicon::BASE;
				$favicon->name = $master->filename;
				$favicon->file = $master;
				$favicon->save();

				Favicon::generateIcons();
			}

			return $data;
		}

		return Theme::i()->getTemplate( 'wizard' )->step1( $form );
	}

	public function _cropImage( $data )
	{
		$stepUrl = Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_crop' );
		$master = Favicon::baseImage();
		$image = \IPS\Image::create( $master->contents() );

		/* Work out which dimensions to suggest */
		if ( $image->width < $image->height )
		{
			$suggestedWidth = $suggestedHeight = $image->width;
		}
		else
		{
			$suggestedWidth = $suggestedHeight = $image->height;
		}

		/* Build form */
		$form = new \IPS\Helpers\Form( 'photo_crop', 'save', $stepUrl->setQueryString( 'action', 'cropPhoto' ) );
		$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_noLabels';
		$form->add(
				new \IPS\Helpers\Form\Custom(
						'photo_crop', [ 0, 0, $suggestedWidth, $suggestedHeight ], TRUE, [
						'getHtml' => function ( $field ) use ( $image, $stepUrl )
						{
							return \IPS\Theme::i()->getTemplate( 'wizard' )->photoCrop(
									$field->name, $field->value,
									$stepUrl->setQueryString( 'do', 'cropPhotoGetPhoto' )->csrf()
							);
						}
				]
				)
		);

		/* Handle submissions */
		if ( $values = $form->values() )
		{
			try
			{
				/* Create new file */
				$image->cropToPoints(
						$values['photo_crop'][0], $values['photo_crop'][1], $values['photo_crop'][2],
						$values['photo_crop'][3]
				);

				/* Save the new */
				$master->replace( (string) $image );

				return $data;
			}
			catch ( \Exception $e )
			{
				$form->error = 'photo_crop_bad';
			}
		}

		/* Display */
		return $form->customTemplate(
				[ call_user_func_array( [ \IPS\Theme::i(), 'getTemplate' ], [ 'forms', 'core' ] ), 'popupTemplate' ]
		);
	}

	/**
	 * Wizard step: Android images
	 *
	 * @param   array $data The current wizard data
	 * @return  string|array
	 */
	public function _stepAndroid( $data )
	{
		$form = new Form(
				'android', 'continue',
				Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_android' )
		);
		$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_vertical';

		$s = \IPS\Settings::i();
		$form->add( new Form\Text( 'favicons_androidAppName', $s->board_name, TRUE ) );
		$form->add( new Form\Color( 'favicons_androidColor', '3C6994' ) );

		# Browser mode
		$form->add(
				new Form\YesNo(
						'favicons_androidStandalone', NULL, FALSE, [
								'togglesOn' => [
										'android_favicons_androidStandalone_startUrl',
										'android_favicons_androidStandalone_orientation'
								]
						]
				)
		);
		$form->add( new Form\Url( 'favicons_androidStandalone_startUrl', NULL ) );
		$form->add(
				new Form\Select(
						'favicons_androidStandalone_orientation', 'default', FALSE, [
								'options' => [
										'default'   => 'favicons_androidStandalone_orientation_default',
										'portrait'  => 'favicons_androidStandalone_orientation_portrait',
										'landscape' => 'favicons_androidStandalone_orientation_landscape'
								]
						]
				)
		);

		if ( $values = $form->values() )
		{
			if ( !isset( Request::i()->ajaxValidate ) )
			{
				$form->saveAsSettings( $values );
				Favicon::generateIcons( Favicon::ANDROID );

				/* Create the manifest.json file */
				$manifest = \IPS\File::create(
						'favicons_Favicons', 'manifest.json', Favicon::androidManifest(), 'favicons', FALSE, NULL, FALSE
				);

				/* Store the manifest filename in settings */
				$s->favicons_androidManifest = (string) $manifest;
				Db::i()->update(
						'core_sys_conf_settings', [ 'conf_value' => (string) $manifest ],
						[ 'conf_key=?', 'favicons_androidManifest' ]
				);
				unset( \IPS\Data\Store::i()->settings );
			}

			return $data;
		}

		return Theme::i()->getTemplate( 'wizard' )->step2( $form );
	}

	/**
	 * Wizard step: iOS images
	 *
	 * @param   array $data The current wizard data
	 * @return  string|array
	 */
	public function _stepIOS( $data )
	{
		$form = new Form(
				'ios', 'continue',
				Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_ios' )
		);
		$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_vertical';

		$form->add( new Form\YesNo( 'favicons_iosFancy', TRUE ) );

		if ( $values = $form->values() )
		{
			if ( !isset( Request::i()->ajaxValidate ) )
			{
				$form->saveAsSettings( $values );
				Favicon::generateIcons( Favicon::IOS );
			}

			return $data;
		}

		return Theme::i()->getTemplate( 'wizard' )->step3( $form );
	}

	/**
	 * Wizard step: Safari images
	 *
	 * @param   array $data The current wizard data
	 * @return  string|array
	 */
	public function _stepSafari( $data )
	{
		$form = new Form(
				'safari', 'continue',
				Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_safari' )
		);
		$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_vertical';

		$form->add(
				new Form\Upload(
						'favicons_safariSvg', NULL, FALSE, [
								'storageExtension' => 'favicons_Favicons',
								'allowedFileTypes' => [ 'svg' ]
						]
				)
		);

		$form->add( new Form\Color( 'favicons_safariTheme', '#5bbad5', TRUE ) );

		if ( $values = $form->values() )
		{
			if ( !isset( Request::i()->ajaxValidate ) )
			{
				if ( $values['favicons_safariSvg'] )
				{
					$contents = $values['favicons_safariSvg']->contents();
					$values['favicons_safariSvg']->delete();
					$file = \IPS\File::create(
							'favicons_Favicons', 'safari-pinned-tab.svg', $contents, 'favicons', FALSE, NULL, FALSE
					);

					$favicon = new Favicon();
					$favicon->type = Favicon::SAFARI;
					$favicon->name = $file->filename;
					$favicon->file = $file;
					$favicon->save();

					unset( $values['favicons_safariSvg'] );
				}

				$form->saveAsSettings( $values );
			}

			return $data;
		}

		return Theme::i()->getTemplate( 'wizard' )->step4( $form );
	}

	/**
	 * Wizard step: Windows images
	 *
	 * @param   array $data The current wizard data
	 * @return  string|array
	 */
	public function _stepWindows( $data )
	{
		$s = \IPS\Settings::i();

		$form = new Form(
				'windows', 'continue',
				Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_windows' )
		);
		$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_vertical';

		# Tile color
		$form->add(
				new Form\Select(
						'favicons_msTileColor', '#2d89ef', FALSE, [
								'options'          => [
										'#2d89ef' => 'favicons_msTileColor_blue',
										'#2b5797' => 'favicons_msTileColor_darkBlue',
										'#00aba9' => 'favicons_msTileColor_teal',
										'#9f00a7' => 'favicons_msTileColor_lightPurple',
										'#603cba' => 'favicons_msTileColor_darkPurple',
										'#b91d47' => 'favicons_msTileColor_darkRed',
										'#da532c' => 'favicons_msTileColor_darkOrange',
										'#ffc40d' => 'favicons_msTileColor_yellow',
										'#00a300' => 'favicons_msTileColor_green'
								],

								// We don't use userSuppliedInput here because we want to be able to display a Color form, not Text
								'unlimited'        => 'custom',
								'unlimitedLang'    => 'favicons_msTileColor_custom',
								'unlimitedToggles' => [ 'windows_favicons_msTileColor_custom' ]
						]
				)
		);
		$form->add( new Form\Color( 'favicons_msTileColor_custom' ) );

		if ( $values = $form->values() )
		{
			if ( !isset( Request::i()->ajaxValidate ) )
			{
				$form->saveAsSettings( $values );
				Favicon::generateIcons( Favicon::WINDOWS );

				/**
				 * Create the browserconfig.xml file
				 * For some reason, it seems we have to manually check for our XML configuration file, though we don't
				 * seem to have to do this for the manifest.json file (and I have no idea why at the moment)
				 */
				try
				{
					$oldFile = \IPS\File::get( 'favicons_Favicons', 'favicons/browserconfig.xml' );
					$oldFile->delete();
				}
				catch ( \Exception $e )
				{
				}

				$browserConfig = \IPS\File::create(
						'favicons_Favicons', 'browserconfig.xml', Favicon::microsoftBrowserConfig(), 'favicons', FALSE,
						NULL, FALSE
				);

				/* Store the browserconfig filename in settings */
				$s->favicons_microsoftBrowserConfig = (string) $browserConfig;
				$s->favicons_setUpComplete = 1;
				Db::i()->update(
						'core_sys_conf_settings', [ 'conf_value' => (string) $browserConfig ],
						[ 'conf_key=?', 'favicons_microsoftBrowserConfig' ]
				);
				Db::i()->update(
						'core_sys_conf_settings', [ 'conf_value' => 1 ], [ 'conf_key=?', 'favicons_setUpComplete' ]
				);
				unset( \IPS\Data\Store::i()->settings );
				Favicon::generateAntiCacheKey();
			}

			return $data;
		}

		return Theme::i()->getTemplate( 'wizard' )->step5( $form );
	}

	/**
	 * Wizard step: Prompt the user to enable / disable rewrites
	 *
	 * @param   array $data The current wizard data
	 * @return  string|array
	 */
	public function _stepRewrites( $data )
	{
		$form = new Form(
				'rewrites', 'continue',
				Url::internal( 'app=favicons&module=favicons&controller=manage&do=wizard&_step=favicons_rewrites' )
		);
		$form->ajaxOutput = TRUE;
		$form->class = 'ipsForm_vertical';

		$form->add( new Form\YesNo( 'favicons_rewrites_enable', TRUE ) );

		if ( $values = $form->values() )
		{
			if ( !isset( Request::i()->ajaxValidate ) )
			{
				$form->saveAsSettings( $values );
			}

			return $data;
		}

		$settingsUrl = (string) Url::internal( 'app=core&module=promotion&controller=seo&tab=urls' );
		$testUrl = Url::baseUrl() . 'apple-touch-icon-144x144.png';

		return Theme::i()->getTemplate( 'wizard' )->step6( $form, $settingsUrl, $testUrl );
	}

	/**
	 * Wizard step: Completion / review
	 *
	 * @param   array $data The current wizard data
	 * @return  string|array
	 */
	public function _stepReview( $data )
	{
		$rfgTestUrl = Url::external( 'http://realfavicongenerator.net/favicon_checker' )->setQueryString(
				'site', Url::baseUrl()
		);
		if ( !Settings::i()->favicons_rewrites_enable )
		{
			$rfgTestUrl = $rfgTestUrl->setQueryString( 'ignore_root_issues', 'on' );
		}
		$rfgTestUrl = $rfgTestUrl->makeSafeForAcp();

		$form = new Form( 'review', 'Complete setup' );
		$form->ajaxOutput = TRUE;
		$form->hiddenValues['finished'] = TRUE;
		if ( $form->values() )
		{
			Output::i()->redirect( Url::internal( 'app=favicons&module=favicons&controller=manage' ) );
		}

		return Theme::i()->getTemplate( 'wizard' )->step7( $rfgTestUrl );
	}

	/**
	 * Favicon Settings
	 *
	 * @return  void
	 */
	public function settings()
	{
		Dispatcher::i()->checkAcpPermission( 'favicons_manage_settings' );

		$s = Settings::i();
		if ( !$s->favicons_setUpComplete )
		{
			Output::i()->error( 'favicons_error_runSetupFirst', '1FAVI201/1', 404 );

			return;
		}

		$form = new Form( 'settings' );

		/**
		 * Android Chrome
		 */
		$form->addTab( 'favicons_androidTab' );
		$form->addHeader( 'favicons_androidHeader' );

		$form->add( new Form\Text( 'favicons_androidAppName', $s->favicons_androidAppName, TRUE ) );
		$form->add( new Form\Text( 'favicons_androidAppShortName', $s->favicons_androidAppShortName, FALSE ) );
		$form->add( new Form\Color( 'favicons_androidColor', $s->favicons_androidColor ) );

		# Browser mode
		$form->add(
				new Form\YesNo(
						'favicons_androidStandalone', $s->favicons_androidStandalone, FALSE, [
								'togglesOn' => [
										'settings_favicons_androidStandalone_startUrl',
										'settings_favicons_androidStandalone_orientation'
								]
						]
				)
		);
		$form->add( new Form\Url( 'favicons_androidStandalone_startUrl', $s->favicons_androidStandalone_startUrl ) );
		$form->add(
				new Form\Select(
						'favicons_androidStandalone_orientation', $s->favicons_androidStandalone_orientation, FALSE, [
								'options' => [
										'default'   => 'favicons_androidStandalone_orientation_default',
										'portrait'  => 'favicons_androidStandalone_orientation_portrait',
										'landscape' => 'favicons_androidStandalone_orientation_landscape'
								]
						]
				)
		);

		/**
		 * iOS - Placeholder, currently has not usable settings
		 */
		$form->addTab( 'favicons_iosTab' );
		$form->addHeader( 'favicons_iosHeader' );

		$form->add( new Form\YesNo( 'favicons_iosFancy', TRUE ) );

		/**
		 * Microsoft Windows 8 and 10
		 */
		$form->addTab( 'favicons_msTab' );
		$form->addHeader( 'favicons_msHeader' );

		# Tile color
		$form->add(
				new Form\Select(
						'favicons_msTileColor', $s->favicons_msTileColor, FALSE, [
								'options'          => [
										'#2d89ef' => 'favicons_msTileColor_blue',
										'#2b5797' => 'favicons_msTileColor_darkBlue',
										'#00aba9' => 'favicons_msTileColor_teal',
										'#9f00a7' => 'favicons_msTileColor_lightPurple',
										'#603cba' => 'favicons_msTileColor_darkPurple',
										'#b91d47' => 'favicons_msTileColor_darkRed',
										'#da532c' => 'favicons_msTileColor_darkOrange',
										'#ffc40d' => 'favicons_msTileColor_yellow',
										'#00a300' => 'favicons_msTileColor_green'
								],
							// We don't use userSuppliedInput here because we want to be able to display a Color form, not Text
								'unlimited'        => 'custom',
								'unlimitedLang'    => 'favicons_msTileColor_custom',
								'unlimitedToggles' => [ 'settings_favicons_msTileColor_custom' ]
						]
				)
		);
		$form->add( new Form\Color( 'favicons_msTileColor_custom', $s->favicons_msTileColor_custom ) );

		/**
		 * Rewrite rules
		 */
		$settingsUrl = (string) Url::internal( 'app=core&module=promotion&controller=seo&tab=urls' );
		$testUrl = Url::baseUrl() . 'apple-touch-icon-144x144.png';

		$form->addTab( 'favicons_rewrites' );
		$form->addHtml( Theme::i()->getTemplate( 'manage' )->rewriteRules( $settingsUrl, $testUrl ) );

		$form->add( new Form\YesNo( 'favicons_rewrites_enable', TRUE ) );

		/**
		 * Save settings
		 */
		if ( $values = $form->values() )
		{
			$form->saveAsSettings( $values );

			/* Regenerate our manifest.json and browserconfig.xml files */
			try
			{
				$oldBrowserConfig = \IPS\File::get( 'favicons_Favicons', 'favicons/browserconfig.xml' );
				$oldBrowserConfig->delete();
			}
			catch ( \Exception $e )
			{
			}

			$manifest = \IPS\File::create(
					'favicons_Favicons', 'manifest.json', Favicon::androidManifest(), 'favicons', FALSE, NULL, FALSE
			);
			$browserConfig = \IPS\File::create(
					'favicons_Favicons', 'browserconfig.xml', Favicon::microsoftBrowserConfig(), 'favicons', FALSE,
					NULL, FALSE
			);

			/* Update our setting values and clear cached data */
			Favicon::generateAntiCacheKey();

			$s->favicons_microsoftBrowserConfig = (string) $browserConfig;
			Db::i()->update(
					'core_sys_conf_settings', [ 'conf_value' => (string) $browserConfig ],
					[ 'conf_key=?', 'favicons_microsoftBrowserConfig' ]
			);

			$s->favicons_androidManifest = (string) $manifest;
			Db::i()->update(
					'core_sys_conf_settings', [ 'conf_value' => (string) $manifest ],
					[ 'conf_key=?', 'favicons_androidManifest' ]
			);

			unset( \IPS\Data\Store::i()->settings );
			unset( \IPS\Data\Store::i()->favicons_html );

			if ( Request::i()->isAjax() )
			{
				Output::i()->json( 'success' );

				return;
			}
			Output::i()->redirect( Url::internal( 'app=favicons&module=favicons&controller=manage' ), '', 302 );
		}

		/**
		 * Output
		 */
		Output::i()->output = $form;
	}


	/**
	 * Edit / replace favicon image
	 *
	 * @return  void
	 */
	public function edit()
	{
		Dispatcher::i()->checkAcpPermission( 'favicons_create' );

		try
		{
			$favicon = Favicon::load( (int) Request::i()->id );
		}
		catch ( \OutOfRangeException $e )
		{
			Output::i()->error( '2FAVI201/2' );

			return;
		}

		$form = new Form( 'settings' );
		$form->add(
				new Form\Upload(
						'favicons_edit_new', NULL, TRUE, [
								'storageExtension' => 'favicons_Favicons',
								'image'            => [
										'maxWidth'  => $favicon->width,
										'maxHeight' => $favicon->height
								],
								'temporary'        => TRUE
						]
				)
		);

		/**
		 * Save settings
		 */
		if ( $values = $form->values() )
		{
			$fileName = $favicon->name;
			$favicon->file->delete();
			$newImage = file_get_contents( $values['favicons_edit_new'] );
			\IPS\File::create( 'favicons_Favicons', $fileName, $newImage, 'favicons', FALSE, NULL, FALSE );
			Favicon::generateAntiCacheKey();

			Output::i()->redirect( Url::internal( 'app=favicons&module=favicons&controller=manage' ), '', 302 );

			return;
		}

		/**
		 * Output
		 */
		Output::i()->output = Theme::i()->getTemplate( 'manage' )->edit( $favicon, $form );
	}

	/**
	 * Reset / delete ALL favicon images and manifest files
	 *
	 * @return  void
	 */
	public function reset()
	{
		if ( !count( Favicon::favicons() ) )
		{
			Output::i()->error( 'favicons_error_noFaviconsToDelete', '1FAVI201/3', 404 );

			return;
		}

		Session::i()->csrfCheck();
		Dispatcher::i()->checkAcpPermission( 'favicons_create' );

		Favicon::reset();
		Output::i()->redirect(
				Url::internal( 'app=favicons&module=favicons&controller=manage' ), 'favicons_reset_success', 302
		);
	}
}