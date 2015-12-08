<?php
/**
 * @brief            File Storage Extension: Favicons
 * @author           <a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license          http://www.invisionpower.com/legal/standards/
 * @package          IPS Social Suite
 * @subpackage       Advanced Favicons
 * @since            15 Nov 2015
 * @version          SVN_VERSION_NUMBER
 */

namespace IPS\favicons\extensions\core\FileStorage;

use IPS\favicons\Favicon;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * File Storage Extension: Favicons
 */
class _Favicons
{
	/**
	 * Count stored files
	 *
	 * @return    int
	 */
	public function count()
	{
		return Favicon::favicons()->count( TRUE );
	}

	/**
	 * Move stored files
	 *
	 * @param	int			$offset					This will be sent starting with 0, increasing to get all files stored by this extension
	 * @param	int			$storageConfiguration	New storage configuration ID
	 * @param	int|NULL	$oldConfiguration		Old storage configuration ID
	 * @throws	\UnderflowException					When file record doesn't exist. Indicating there are no more files to move
	 * @return	void|int							An offset integer to use on the next cycle, or nothing
	 */
	public function move( $offset, $storageConfiguration, $oldConfiguration = NULL )
	{
		$favicons = Favicon::favicons();

		foreach ( $favicons as $favicon )
		{
			if ( $oldConfiguration )
			{
				\IPS\File::get( $oldConfiguration ?: 'favicons_Favicons', (string) $favicon->file )->move(
					$storageConfiguration
				);
			}
			else
			{
				$favicon->file->move( $storageConfiguration );
			}
		}
	}

	/**
	 * Check if a file is valid
	 *
	 * @param    \IPS\Http\Url $file The file to check
	 * @return    bool
	 */
	public function isValidFile( $file )
	{
		try
		{
			Favicon::loadByFile( $file );
		}
		catch ( \OutOfRangeException $e )
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Delete all stored files
	 *
	 * @return    void
	 */
	public function delete()
	{
		$favicons = Favicon::favicons();

		foreach ( $favicons as $favicon )
		{
			$favicon->delete();
		}
	}
}