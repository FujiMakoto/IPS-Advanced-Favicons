<?php

namespace IPS\favicons;

\IPS\IPS::$PSR0Namespaces['PHP_ICO'] = \IPS\ROOT_PATH . '/applications/favicons/sources/3rd_party/PHP_ICO';

use IPS\Db;
use IPS\File;
use IPS\Settings;

class _Favicon extends \IPS\Patterns\ActiveRecord
{
	/**
	 * Favicon types
	 */
	const BASE    = 0;
	const ANDROID = 1;
	const IOS     = 2;
	const SAFARI  = 3;
	const WINDOWS = 4;
	const MASTER  = 9;

	/**
	 * @brief   Standard favicon sizes
	 */
	public static $masterSizes = [
			[ 16, 16 ],
			[ 32, 32 ],
			[ 96, 96 ]
	];

	/**
	 * @brief   Standard filename template
	 */
	public static $masterNameTemplate = 'favicon-%dx%d.%s';

	/**
	 * @brief   Standard HTML template
	 */
	public static $masterHtmlTemplate = '<link rel="icon" type="%3$s" href="%1$s" sizes="%2$s">';

	/**
	 * @brief   Android favicon sizes
	 */
	public static $androidSizes = [
			[ 36, 36 ],
			[ 48, 48 ],
			[ 72, 72 ],
			[ 96, 96 ],
			[ 144, 144 ],
			[ 192, 192 ],
	];

	/**
	 * @brief   Android filename template
	 */
	public static $androidNameTemplate = 'android-chrome-%dx%d.%s';

	/**
	 * @brief   Android HTML template
	 */
	public static $androidHtmlTemplate = '<link rel="icon" type="%3$s" href="%1$s" sizes="%2$s">';

	/**
	 * @brief   Apple favicon sizes
	 */
	public static $appleSizes = [
			[ 57, 57 ],
			[ 60, 60 ],
			[ 72, 72 ],
			[ 76, 76 ],
			[ 114, 114 ],
			[ 120, 120 ],
			[ 144, 144 ],
			[ 152, 152 ],
			[ 180, 180 ],
	];

	/**
	 * @brief   Apple filename template
	 */
	public static $appleNameTemplate = 'apple-touch-icon-%dx%d.%s';

	/**
	 * @brief   Apple HTML template
	 */
	public static $appleHtmlTemplate = '<link rel="apple-touch-icon" sizes="%2$s" href="%1$s">';

	/**
	 * @brief   Windows favicon sizes
	 */
	public static $windowsSizes = [
			[ 144, 144 ]
	];

	/**
	 * @brief   Windows filename template
	 */
	public static $windowsNameTemplate = 'mstile-%dx%d.%s';

	/**
	 * @brief   Windows HTML template
	 */
	public static $windowsHtmlTemplate = '<meta name="msapplication-TileImage" content="%1$s">';

	/**
	 * @brief   Database Table
	 */
	public static $databaseTable = 'favicons_favicons';

	/**
	 * @brief   Multiton Store
	 */
	protected static $multitons;

	/**
	 * @brief   Default Values
	 */
	protected static $defaultValues = [ ];

	/**
	 * @brief   Database Column Map
	 */
	public static $databaseColumnMap = [ ];

	/**
	 * @brief   File cache
	 */
	protected $_file;

	/**
	 * Get favicon file object
	 *
	 * @return  \IPS\File
	 */
	protected function get_file()
	{
		return $this->_file ?: $this->_file = File::get( 'favicons_Favicons', $this->_data['file'] );
	}

	/**
	 * Set the favicon file path and automatically assign the width / height attributes
	 *
	 * @param   \IPS\File $file
	 * @return  void
	 */
	protected function set_file( File $file )
	{
		$this->_data['file'] = (string) $file;
		$this->_file = NULL;

		if ( $file->filename == 'favicon.ico' )
		{
			$this->width = '48';
			$this->height = '48';

			return;
		}

		try
		{
			$image = \IPS\Image::create( $file->contents() );
			$this->width = $image->width;
			$this->height = $image->height;
		}
		catch ( \InvalidArgumentException $e )
		{
		}
	}

	/**
	 * Get favicon {width}x{height} string representation
	 *
	 * @return  string
	 */
	protected function get_sizes()
	{
		return "{$this->width}x{$this->height}";
	}

	/**
	 * Get favicon HTML
	 *
	 * @return  null|string
	 */
	protected function get_html()
	{
		$type = NULL;

		/* Template overrides */
		if ( $this->type === static::MASTER )
		{
			$type = 'master';

			if ( ( $this->file->filename == 'favicon.ico' ) )
			{
				if ( Settings::i()->favicons_rewrites_enable )
				{
					return NULL;
				}

				return '<link rel="shortcut icon" href="' . htmlspecialchars( $this->getFileUrl( $this->file ) ) . '">';
			}
		}

		if ( $this->type === static::ANDROID )
		{
			$type = 'android';

			if ( ( $this->width != 192 ) and ( $this->height != 192 ) )
			{
				return NULL;
			}
		}

		if ( $this->type === static::WINDOWS )
		{
			$type = 'windows';
		}

		if ( $this->type === static::SAFARI )
		{
			return '<link rel="mask-icon" href="' . htmlspecialchars( $this->getFileUrl( $this->file ) ) . '" color="' .
			Settings::i()->favicons_safariTheme . '">';
		}

		if ( $this->type === static::IOS )
		{
			$type = 'apple';
		}

		/* Format the HTML output and return */
		if ( $type )
		{
			$htmlTemplate = "{$type}HtmlTemplate";
			$url = htmlspecialchars( $this->getFileUrl( $this->file ) );

			return sprintf( static::$$htmlTemplate, $url, $this->sizes, File::getMimeType( (string) $this->file ) );
		}
	}

	/**
	 * Retrieve the favicon files URL with an anti-cache key appended to it
	 *
	 * @param   \IPS\File|NULL $file
	 * @return  string
	 */
	public function getFileUrl( $file = NULL )
	{
		$file = $file ?: $this->file;
		$antiCacheKey = Settings::i()->favicons_antiCacheKey;

		return (string) $file->url->setQueryString( 'v', $antiCacheKey );
	}

	/**
	 * Get all available favicons
	 *
	 * @param   int|null $type Optional device type to filter by.
	 * @return  Favicon[]
	 */
	public static function favicons( $type = NULL )
	{
		$where = $type ? [ 'type=?', (int) $type ] : NULL;
		$select = Db::i()->select( '*', static::$databaseTable, $where );

		$return = [ ];
		foreach ( $select as $row )
		{
			$return[] = static::constructFromData( $row );
		}

		return $return;
	}

	/**
	 * Reset / delete all favicons and manifest files
	 *
	 * @return  void
	 */
	public static function reset()
	{
		$favicons = static::favicons();

		foreach ( $favicons as $favicon )
		{
			$favicon->delete();
		}

		/* Reset the application setup status */
		Settings::i()->favicons_setUpComplete = 0;
		Db::i()->update( 'core_sys_conf_settings', [ 'conf_value' => 0 ], [ 'conf_key=?', 'favicons_setUpComplete' ] );
		unset( \IPS\Data\Store::i()->settings );

		/* Clear any cached HTML output for favicons */
		unset( \IPS\Data\Store::i()->favicons_html );
		unset( \IPS\Data\Store::i()->favicons_urls );
	}

	/**
	 * Get the HTML output for *all* favicons and manifest files
	 *
	 * @param   bool|FALSE $ignoreCache Ignore HTML output cache
	 * @return  string
	 */
	public static function html( $ignoreCache = FALSE )
	{
		if ( !$ignoreCache and isset( \IPS\Data\Store::i()->favicons_html ) )
		{
			return \IPS\Data\Store::i()->favicons_html;
		}

		$html = '';

		$favicons = static::favicons();
		foreach ( $favicons as $favicon )
		{
			if ( $result = $favicon->html )
			{
				$html = $html . $result;
			}
		}

		/* Android extra data */
		if ( $manifest = static::androidManifestHtml() )
		{
			$html = $html . $manifest;
		}

		/* Microsoft extra data */
		if ( $browserConfig = static::microsoftBrowserConfigHtml() )
		{
			$html = $html . $browserConfig;
		}

		if ( !$ignoreCache )
		{
			\IPS\Data\Store::i()->favicons_html = $html;
		}

		return $html;
	}

	/**
	 * Load a favicon by its filename
	 *
	 * @param   string $file
	 * @return  Favicon
	 */
	public static function loadByFile( $file )
	{
		$result = Db::i()->select( '*', static::$databaseTable, [ 'file=?', (string) $file ] )->first();

		return static::constructFromData( $result );
	}

	/**
	 * Load a favicon by its name
	 *
	 * @param   string $name
	 * @return  Favicon
	 */
	public static function loadByName( $name )
	{
		$result = Db::i()->select( '*', static::$databaseTable, [ 'name=?', (string) $name ] )->first();

		return static::constructFromData( $result );
	}

	/**
	 * Get the master image
	 *
	 * @return  File
	 */
	public static function baseImage()
	{
		$file = Db::i()->select( 'file', static::$databaseTable, [ 'type=?', static::BASE ], 'id DESC' )->first();

		return File::get( 'favicons_Favicons', $file );
	}

	/**
	 * Generate icons for the specified device type
	 *
	 * @param   int $for The device type to generate icons for
	 * @return  void
	 */
	public static function generateIcons( $for = self::MASTER )
	{
		switch ( $for )
		{
			case static::MASTER:
				$type = 'master';
				break;

			case static::ANDROID:
				$type = 'android';
				break;

			case static::SAFARI:
			case static::IOS:
				$type = 'apple';
				break;

			case static::WINDOWS:
				$type = 'windows';
				break;

			default:
				throw new \UnexpectedValueException( 'Unrecognized icon type' );
		}

		$sizes = "{$type}Sizes";
		$nameTemplate = "{$type}NameTemplate";

		/* Can we support our master images filetype? */
		$master = static::baseImage();
		switch ( File::getMimeType( (string) $master ) )
		{
			case 'image/x-icon':
			case 'image/gif':
			case 'image/png':
				$ext = 'png';
				break;

			default:
				$ext =
						'jpeg';
				break;
		}

		/* Generate the favicons */
		foreach ( static::$$sizes as $size )
		{
			list( $width, $height ) = $size;

			$favicon = \IPS\Image::create( $master->contents() );
			$maxSize = max( $favicon->width, $favicon->height );
			// TODO: We should handle this better, this can result in broken transparency
			$favicon->resizeToMax( $maxSize, $maxSize );
			$favicon->crop( $width, $height );

			$filename = sprintf( static::$$nameTemplate, $width, $height, $ext );

			$file = File::create( 'favicons_Favicons', $filename, (string) $favicon, 'favicons', FALSE, NULL, FALSE );

			/* Save the new favicon record */
			$record = new Favicon();

			$record->type = $for;
			$record->name = $file->filename;
			$record->file = $file;

			$record->save();
		}

		/* Standard specific patch-in */
		if ( $for === static::MASTER )
		{
			$image = \IPS\Image::create( $master->contents() );
			$maxSize = max( $image->width, $image->height );
			$image->resizeToMax( $maxSize, $maxSize );
			$image->crop( 48, 48 );

			$ico = new \PHP_ICO\PHP_ICO();
			$ico->PHP_ICO();
			$ico->add_image( (string) $image, [ [ 48, 48 ], [ 32, 32 ], [ 16, 16 ] ] );

			$file = File::create( 'favicons_Favicons', 'favicon.ico', $ico->get_ico(), 'favicons', FALSE, NULL, FALSE );

			/* Save the new favicon record */
			$record = new Favicon();

			$record->type = Favicon::MASTER;
			$record->name = $file->filename;
			$record->file = $file;

			$record->save();
		}

		/* Apple specific patch-in */
		if ( $for === static::IOS )
		{
			$favicon = \IPS\Image::create( $master->contents() );
			$maxSize = max( $favicon->width, $favicon->height );
			$favicon->resizeToMax( $maxSize, $maxSize );
			$favicon->crop( 180, 180 );

			foreach ( [ 'apple-touch-icon.%s', 'apple-touch-icon-precomposed.%s' ] as $filenameTemplate )
			{
				$filename = sprintf( $filenameTemplate, $ext );
				$file = File::create(
						'favicons_Favicons', $filename, (string) $favicon, 'favicons', FALSE, NULL, FALSE
				);

				/* Save the new favicon record */
				$record = new Favicon();

				$record->type = Favicon::IOS;
				$record->name = $file->filename;
				$record->file = $file;

				$record->save();
			}
		}

		/* Microsoft specific patch-ins */
		if ( $for === static::WINDOWS )
		{
			$sizes = [
					[
							'name'   => 'mstile-150x150.png',
							'canvas' => [ 'w' => '270', 'h' => 270 ],
							'size'   => [ 'w' => '135', 'h' => 135 ],
							'offset' => [ 'x' => '-67', 'y' => '-45' ],
							'public' => [ 'w' => '150', 'h' => '150' ]
					],
					[
							'name'   => 'mstile-310x150.png',
							'canvas' => [ 'w' => '558', 'h' => 270 ],
							'size'   => [ 'w' => '126', 'h' => 126 ],
							'offset' => [ 'x' => '-216', 'y' => '-50' ],
							'public' => [ 'w' => '310', 'h' => '150' ]
					],
					[
							'name'   => 'mstile-310x310.png',
							'canvas' => [ 'w' => '558', 'h' => 558 ],
							'size'   => [ 'w' => '259', 'h' => 259 ],
							'offset' => [ 'x' => '-149', 'y' => '-128' ],
							'public' => [ 'w' => '310', 'h' => '310' ]
					],
					[
							'name'   => 'mstile-70x70.png',
							'canvas' => [ 'w' => '128', 'h' => 128 ],
							'size'   => [ 'w' => '95', 'h' => 95 ],
							'offset' => [ 'x' => '-16', 'y' => '-16' ],
							'public' => [ 'w' => '70', 'h' => '70' ]
					]
			];

			foreach ( $sizes as $size )
			{
				// Create the image
				$favicon = \IPS\Image::create( $master->contents() );
				$maxSize = max( $favicon->width, $favicon->height );
				$favicon->resizeToMax( $maxSize, $maxSize );
				$favicon->crop( $size['size']['w'], $size['size']['h'] );

				$canvas = imagecreatetruecolor( $size['canvas']['w'], $size['canvas']['h'] );

				imagealphablending( $canvas, FALSE );
				imagesavealpha( $canvas, TRUE );

				$trans_colour = imagecolorallocatealpha( $canvas, 0, 0, 0, 127 );
				imagefill( $canvas, 0, 0, $trans_colour );

				// Place our image on the canvas
				imagecopy(
						$canvas, imagecreatefromstring( (string) $favicon ), abs( $size['offset']['x'] ),
						abs( $size['offset']['y'] ), 0, 0, $favicon->width, $favicon->height
				);

				// Render the output image
				ob_start();
				imagepng( $canvas );
				$result = ob_get_clean();
				imagedestroy( $canvas );

				// Save the result
				$file = File::create( 'favicons_Favicons', $size['name'], $result, 'favicons', FALSE, NULL, FALSE );

				/* Save the new favicon record */
				$record = new Favicon();

				$record->type = Favicon::WINDOWS;
				$record->name = $file->filename;
				$record->file = $file;
				$record->width = $size['public']['w'];
				$record->height = $size['public']['h'];

				$record->save();
			}
		}
	}

	/**
	 * Generate Android manifest.json data
	 *
	 * @param   bool|TRUE $json Return output in json encoded format
	 * @return  array|string
	 */
	public static function androidManifest( $json = TRUE )
	{
		$s = Settings::i();

		$manifest = [
				'name'  => $s->favicons_androidAppName,
				'icons' => [ ]
		];

		if ( $s->favicons_androidAppShortName )
		{
			$manifest['short_name'] = $s->favicons_androidAppShortName;
		}

		/* Additional data to be added only if standalone mode is enabled */
		if ( $s->favicons_androidStandalone )
		{
			$manifest['display'] = 'standalone';

			$startUrl = $s->favicons_androidStandalone_startUrl;
			if ( $startUrl )
			{
				$manifest['start_url'] = (string) $startUrl;
			}

			$orientation = $s->favicons_androidStandalone_orientation;
			if ( $orientation and ( $orientation != 'default' ) )
			{
				$manifest['orientation'] = $orientation;
			}
		}

		/* Parse icons */
		$favicons = static::favicons( static::ANDROID );
		foreach ( $favicons as $favicon )
		{
			$manifest['icons'][] = [
					'src'   => (string) $favicon->file->url,
					'sizes' => $favicon->sizes,
					'type'  => File::getMimeType( (string) $favicon->file )
			];
		}

		return $json ? json_encode( $manifest ) : $manifest;
	}

	/**
	 * Get the HTML output for Android's manifest.json
	 *
	 * @return  string
	 */
	public static function androidManifestHtml()
	{
		$s = Settings::i();

		if ( $url = $s->favicons_androidManifest )
		{
			try
			{
				$file = File::get( 'favicons_Favicons', $url );
				$url = (string) $file->url;
				$html = "<link rel='manifest' href='{$url}'>";

				if ( $themeColor = $s->favicons_androidColor )
				{
					$html = $html . "<meta name='theme-color' content='{$themeColor}'>";
				}

				return $html;
			}
			catch ( \Exception $e )
			{
				\IPS\Log::i( \LOG_DEBUG )->write(
						'Favicons Error : ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 'favicons_error'
				);
			}
		}
	}

	/**
	 * Generate Microsoft browserconfig.xml data
	 *
	 * @return  string
	 */
	public static function microsoftBrowserConfig()
	{
		$s = Settings::i();

		$xml = new \SimpleXMLElement( '<?xml version="1.0" encoding="utf-8"?><browserconfig/>' );
		$msApplication = $xml->addChild( 'msapplication' );
		$tile = $msApplication->addChild( 'tile' );

		$favicons = static::favicons( static::WINDOWS );
		foreach ( $favicons as $favicon )
		{
			if ( $favicon->width == $favicon->height )
			{
				$item = $tile->addChild( "square{$favicon->sizes}logo" );
			}
			else
			{
				$item = $tile->addChild( "wide{$favicon->sizes}logo" );
			}
			$item->addAttribute( 'src', (string) $favicon->file->url );
		}

		$tileColor =
				( $s->favicons_msTileColor == 'custom' ) ? $s->favicons_msTileColor_custom : $s->favicons_msTileColor;
		$tile->addChild( 'TileColor', $tileColor );

		return $xml->asXML();
	}

	/**
	 * Get the HTML output for Microsoft's browserconfig.xml
	 *
	 * @return  string
	 */
	public static function microsoftBrowserConfigHtml()
	{
		$s = Settings::i();

		if ( $url = $s->favicons_microsoftBrowserConfig )
		{
			try
			{
				$file = File::get( 'favicons_Favicons', $url );
				$url = (string) $file->url;
				$html = "<meta name='msapplication-config' content='{$url}'>";

				if ( $tileColor = $s->favicons_msTileColor )
				{
					if ( $tileColor == 'custom' )
					{
						$tileColor = $s->favicons_msTileColor_custom ?: '#ffffff';
					}

					$html = $html . "<meta name='msapplication-TileColor' content='{$tileColor}'>";
				}

				return $html;
			}
			catch ( \Exception $e )
			{
				\IPS\Log::i( \LOG_DEBUG )->write(
						'Favicons Error : ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 'favicons_error'
				);
			}
		}
	}

	/**
	 * Generate a random string for use with anti-cache keys
	 *
	 * @param   int  $length Anti-cache key length. Defaults to 10.
	 * @param   bool $update Automatically update the anti-cache key setting. Defaults to True.
	 * @return  string
	 */
	public static function generateAntiCacheKey( $length = 10, $update = TRUE )
	{
		$key = bin2hex( openssl_random_pseudo_bytes( $length / 2 ) );

		/* Update the setting value */
		if ( $update )
		{
			Settings::i()->favicons_antiCacheKey = $key;
			Db::i()->update(
					'core_sys_conf_settings', [ 'conf_value' => $key ], [ 'conf_key=?', 'favicons_antiCacheKey' ]
			);
			unset( \IPS\Data\Store::i()->settings );
		}

		return $key;
	}

	/**
	 * [ActiveRecord] Delete Record
	 *
	 * @return    void
	 */
	public function delete()
	{
		$this->file->delete();
		parent::delete();
	}
}