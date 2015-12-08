<?php


namespace IPS\favicons\modules\front\favicons;

use IPS\favicons\Favicon;
use IPS\Http\Url;
use IPS\Output;
use IPS\Request;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Favicon router
 */
class _router extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return    void
	 */
	public function execute()
	{
		parent::execute();
	}

	/**
	 * Find and redirect to our favicon, or throw an error if it does not exist
	 *
	 * @param   array       $urls
	 * @param   string|null $filenameTemplate
	 * @param   string|null $default
	 */
	protected function findOrFail( $urls, $filenameTemplate=NULL, $default=NULL )
	{
		$width  = (int) Request::i()->width;
		$height = (int) Request::i()->height;

		if ( !$width or !$height )
		{
			// If we expect a default favicon without any size attributes, try and redirect to it
			if ( ( $default !== NULL ) and isset( $urls[ $default ] ) )
			{
				Output::i()->redirect( Url::external( $urls[ $default ] ) );
				return;
			}

			Output::i()->error( 'node_error', '2FAVI203/2' );
		}

		// Format the filename and redirect to it if it exists
		if ( $filenameTemplate !== NULL )
		{
			$filename = sprintf( $filenameTemplate, $width, $height );
			if ( isset( $urls[ $filename ] ) )
			{
				Output::i()->redirect( Url::external( $urls[ $filename ] ) );
				return;
			}
		}

		// If we're still here, we couldn't find our favicon
		Output::i()->error( 'node_error', '2FAVI203/3' );
	}

	/**
	 * Route favicons requested at the root level
	 *
	 * @return    void
	 */
	protected function manage()
	{
		$default = NULL;
		$filenameTemplate = NULL;
		$type = Request::i()->type;

		if ( !$type )
		{
			Output::i()->error( 'node_error', '2FAVI203/1' );
			return;
		}

		/**
		 * Get an array of URL maps
		 */
		try
		{
			$urls = json_decode( \IPS\Data\Store::i()->favicons_urls, TRUE );
		}
		catch ( \OutOfRangeException $e )
		{
			$urls = [ ];
			$favicons = Favicon::favicons();
			foreach ( $favicons as $favicon )
			{
				$urls[ $favicon->name ] = (string) $favicon->file->url;
			}

			\IPS\Data\Store::i()->favicons_urls = json_encode( $urls );
		}

		// Get the filename attributes
		switch ( $type )
		{
			case 'master':
				$default = 'favicon.ico';
				$filenameTemplate = 'favicon-%sx%s.png';
				break;

			case 'apple':
				$default = 'apple-touch-icon.png';
				$filenameTemplate = 'apple-touch-icon-%sx%s.png';
				break;

			case 'apple_precomposed':
				$default = 'apple-touch-icon-precomposed.png';
				break;

			case 'android':
				$filenameTemplate = 'android-chrome-%sx%s.png';
				break;

			case 'microsoft':
				$filenameTemplate = 'mstile-%sx%s.png';
		}

		$this->findOrFail( $urls, $filenameTemplate, $default );
	}
}