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
	 * Route favicons requested at the root level
	 *
	 * @return    void
	 */
	protected function manage()
	{
		$width = (int) Request::i()->width;
		$height = (int) Request::i()->height;
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

		/**
		 * Standard
		 */
		if ( $type == 'master' )
		{
			if ( !$width or !$height )
			{
				if ( isset( $urls['favicon.ico'] ) )
				{
					Output::i()->redirect( Url::external( $urls['favicon.ico'] ) );

					return;
				}
				else
				{
					Output::i()->error( 'node_error', '2FAVI203/2' );

					return;
				}
			}

			if ( isset( $urls["favicon-{$width}x{$height}.png"] ) )
			{
				Output::i()->redirect( Url::external( $urls["favicon-{$width}x{$height}.png"] ) );

				return;
			}
			else
			{
				Output::i()->error( 'node_error', '2FAVI203/3' );

				return;
			}
		}

		/**
		 * Apple
		 */
		if ( $type == 'apple' )
		{
			if ( !$width or !$height )
			{
				if ( isset( $urls['apple-touch-icon.png'] ) )
				{
					/*$favicon = Favicon::loadByName( 'apple-touch-icon.png' );
					Output::i()->sendOutput( $favicon->file->contents(), 200, 'image/png' );
					return;*/

					Output::i()->redirect( Url::external( $urls['apple-touch-icon.png'] ) );

					return;
				}
				else
				{
					Output::i()->error( 'node_error', '2FAVI203/4' );

					return;
				}
			}

			if ( isset( $urls["apple-touch-icon-{$width}x{$height}.png"] ) )
			{
				/*$favicon = Favicon::loadByName( "apple-touch-icon-{$width}x{$height}.png" );
				Output::i()->sendOutput( $favicon->file->contents(), 200, 'image/png' );
				return;*/

				Output::i()->redirect( Url::external( $urls["apple-touch-icon-{$width}x{$height}.png"] ) );

				return;
			}
			else
			{
				Output::i()->error( 'node_error', '2FAVI203/5' );

				return;
			}
		}

		if ( $type == 'apple_precomposed' )
		{
			if ( isset( $urls["apple-touch-icon-precomposed.png"] ) )
			{
				/*$favicon = Favicon::loadByName( 'apple-touch-icon-precomposed.png' );
				Output::i()->sendOutput( $favicon->file->contents(), 200, 'image/png' );
				return;*/

				Output::i()->redirect( Url::external( $urls["apple-touch-icon-precomposed.png"] ) );

				return;
			}
			else
			{
				Output::i()->error( 'node_error', '2FAVI203/6' );

				return;
			}
		}

		/**
		 * Android
		 */
		if ( $type == 'android' )
		{
			if ( isset( $urls["android-chrome-{$width}x{$height}.png"] ) )
			{
				Output::i()->redirect( Url::external( $urls["android-chrome-{$width}x{$height}.png"] ) );

				return;
			}
			else
			{
				Output::i()->error( 'node_error', '2FAVI203/7' );

				return;
			}
		}

		/**
		 * Windows
		 */
		if ( $type == 'microsoft' )
		{
			if ( isset( $urls["mstile-{$width}x{$height}.png"] ) )
			{
				Output::i()->redirect( Url::external( $urls["mstile-{$width}x{$height}.png"] ) );

				return;
			}
			else
			{
				Output::i()->error( 'node_error', '2FAVI203/8' );

				return;
			}
		}


		Output::i()->error( 'node_error', '2FAVI203/9' );
	}
}