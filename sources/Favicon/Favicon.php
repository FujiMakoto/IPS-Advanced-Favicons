<?php

namespace IPS\favicons;
\IPS\IPS::$PSR0Namespaces['PHP_ICO'] = \IPS\ROOT_PATH . '/applications/favicons/sources/3rd_party/PHP_ICO';

use IPS\Db;
use IPS\File;

class _Favicon extends \IPS\Patterns\ActiveRecord
{
	/**
	 * Favicon types
	 */
	const BASE      = 0;
	const ANDROID   = 1;
	const IOS       = 2;
	const SAFARI    = 3;
	const WINDOWS   = 4;
	const MASTER    = 9;

	/**
	 * @brief   Standard favicon sizes
	 */
	public static $masterSizes = [
		[16, 16],
		[32, 32],
		[96, 96]
	];

	/**
	 * @brief   Standard filename template
	 */
	public static $masterNameTemplate = 'favicon-%d-%d.%s';
	
	/**
	 * @brief   Android favicon sizes
	 */
	public static $androidSizes = [
		[36, 36],
		[48, 48],
		[72, 72],
		[96, 96],
		[144, 144],
		[192, 192],
	];

	/**
	 * @brief   Android filename template
	 */
	public static $androidNameTemplate = 'android-chrome-%d-%d.%s';

	/**
	 * @brief   Apple favicon sizes
	 */
	public static $appleSizes = [
		[57, 57],
		[60, 60],
		[72, 72],
		[76, 76],
		[120, 120],
		[144, 144],
		[152, 152],
		[180, 180],
	];

	/**
	 * @brief   Apple filename template
	 */
	public static $appleNameTemplate = 'apple-touch-icon-%d-%d.%s';

	/**
	 * @brief   Apple favicon sizes
	 */
	public static $windowsSizes = [
			[70, 70],
			[144, 144]
	];

	/**
	 * @brief   Apple filename template
	 */
	public static $windowsNameTemplate = 'mstile-%d-%d.%s';

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
	protected static $defaultValues = array();

	/**
	 * @brief   Database Column Map
	 */
	public static $databaseColumnMap = array();

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
		return $this->_file ?: $this->_file = \IPS\File::get( 'favicons_Favicons', $this->_data['file'] );
	}

	/**
	 * Set the favicon file path and automatically assign the width / height attributes
	 *
	 * @param   \IPS\File   $file
	 * @return  void
	 */
	protected function set_file( \IPS\File $file )
	{
		$this->_data['file'] = (string) $file;
		$this->_file = NULL;

		try
		{
			$image = \IPS\Image::create( $file->contents() );
			$this->width  = $image->width;
			$this->height = $image->height;
		}
		catch ( \InvalidArgumentException $e ) {}
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
	 * @param   int|null    $type   Optional favicon type to filter by.
	 * @return  \IPS\Db\Select
	 */
	public static function favicons( $type = NULL )
	{
		$where = $type ? [ 'type=?', (int) $type ] : NULL;
		$select = Db::i()->select( '*', static::$databaseTable, $where );

		$return = [];
		foreach ( $select as $row )
		{
			$return[] = static::constructFromData( $row );
		}

		return $return;
	}

	/**
	 * Get the master image
	 *
	 * @return  \IPS\File
	 */
	public static function baseImage()
	{
		$file = Db::i()->select( 'file', static::$databaseTable, [ 'type=?', static::BASE ], 'id DESC' )->first();
		return \IPS\File::get( 'favicons_Favicons', $file );
	}

	/**
	 * Load a favicon by its filename
	 *
	 * @param   string  $file
	 * @return  Favicon
	 */
	public static function loadByFile( $file )
	{
		$result = Db::i()->select( '*', static::$databaseTable, [ 'file=?', (string) $file ] )->first();
		return static::constructFromData( $result );
	}

	public static function reset()
	{
		$favicons = static::favicons();

		foreach ( $favicons as $favicon )
		{
			$favicon->delete();
		}
	}

	public static function generateIcons( $for=self::MASTER )
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
				// $type = 'image/png';
				$ext  = 'png';
				break;

			default:
				// $type = 'image/jpeg';
				$ext  = 'jpg';
				break;
		}

		/* Generate the favicons */
		foreach ( static::$$sizes as $size )
		{
			list( $width, $height ) = $size;

			$favicon = \IPS\Image::create( $master->contents() );
			$favicon->resize( $width, $height );
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
			$image->resize( 48, 48 );

			$ico = new \PHP_ICO\PHP_ICO();
			$ico->PHP_ICO();
			$ico->add_image( (string) $image );

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
			$favicon->resize( 180, 180 );

			foreach ( ['apple-touch-icon.%s', 'apple-touch-icon-precomposed.%s'] as $filenameTemplate )
			{
				$filename = sprintf( $filenameTemplate, $ext );
				$file = File::create( 'favicons_Favicons', $filename, (string) $favicon, 'favicons', FALSE, NULL, FALSE );

				/* Save the new favicon record */
				$record = new Favicon();

				$record->type = Favicon::IOS;
				$record->name = $file->filename;
				$record->file = $file;

				$record->save();
			}
		}
	}

	/**
	 * Generate Android manifest.json data
	 *
	 * @param   bool|TRUE   $json   Return output in json encoded format
	 * @return  array|string
	 */
	public static function androidManifest( $json=TRUE )
	{
		$s = \IPS\Settings::i();

		$manifest = [
				'name'  => $s->favicons_androidAppName,
				'icons' => []
		];

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
	 * Generate Microsoft browserconfig.xml data
	 *
	 * @return  string
	 */
	public static function microsoftBrowserConfig()
	{
		$s = \IPS\Settings::i();

		$xml = new \SimpleXMLElement( '<xml/>' );
		$browserConfig = $xml->addChild( 'browserconfig' );
		$msApplication = $browserConfig->addChild( 'msapplication' );
		$tile = $msApplication->addChild( 'tile' );

		$favicons = static::favicons( static::WINDOWS );
		foreach ( $favicons as $favicon )
		{
			$item = $tile->addChild( "square{$favicon->sizes}logo" );
			$item->addAttribute( 'src', (string) $favicon->file->url );
		}

		$tile->addChild( 'TileColor', $s->favicons_msTileColor );
		return $xml->asXML();
	}

	/**
	 * [ActiveRecord] Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		$this->file->delete();
		parent::delete();
	}
}