<?php

namespace IPS\favicons;

use IPS\Db;
use IPS\File;

class _Favicon extends \IPS\Patterns\ActiveRecord
{
	/**
	 * Favicon types
	 */
	const ANDROID   = 1;
	const IOS       = 2;
	const SAFARI    = 3;
	const WINDOWS   = 4;
	const MASTER    = 9;

	/**
	 * @brief   Android favicon sizes
	 */
	public static $androidSizes = [
		['36', '36'],
		['48', '48'],
		['72', '72'],
		['96', '96'],
		['144', '144'],
		['192', '192'],
	];

	/**
	 * @brief   Android filename template
	 */
	public static $androidNameTemplate = 'android-chrome-%d-%d.%s';

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

		$image = \IPS\Image::create( $file->contents() );
		$this->width  = $image->width;
		$this->height = $image->height;
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
	public static function master()
	{
		$file = Db::i()->select( 'file', static::$databaseTable, [ 'type=?', static::MASTER ], 'id DESC' )->first();
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

	public static function generateForAndroid()
	{
		$master = static::master();

		/* Can we support our master images filetype? */
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
		foreach ( static::$androidSizes as $size )
		{
			list( $width, $height ) = $size;

			$favicon = \IPS\Image::create( $master->contents() );
			$favicon->resize( $width, $height );
			$filename = sprintf( static::$androidNameTemplate, $width, $height, $ext );

			$file = File::create( 'favicons_Favicons', $filename, (string) $favicon, 'favicons', FALSE, NULL, FALSE );

			/* Save the new favicon record */
			$record = new Favicon();

			$record->type = Favicon::ANDROID;
			$record->name = $file->filename;
			$record->file = $file;

			$record->save();
		}

		/* Create the manifest.json file */
		File::create( 'favicons_Favicons', 'manifest.json', static::androidManifest(), 'favicons', FALSE, NULL, FALSE );
	}

	/**
	 * Generate android manifest.json data
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