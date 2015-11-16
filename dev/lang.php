<?php

$lang = array(
	'__app_favicons'	=> "Advanced Favicons",

	'menu__favicons_favicons'   => 'Favicons',

	/**
	 * [Admin] Settings
	 */
	'menu__favicons_favicons_settings'  => 'Settings',

	# Android
	'favicons_androidTab'       => 'Android',
	'favicons_androidHeader'    => 'Android Chrome',

	'favicons_androidAppName'           => 'App name',
	'favicons_androidAppShortName'      => 'App short name',
	'favicons_androidAppShortName_desc' => 'Optional. The short name is preferred over name and if provided will be used.',
	'favicons_androidColor'             => 'Theme color',
	'favicons_androidColor_desc'        => 'Starting with Android Lollipop, you can customize the color of the task bar in the switcher.',

	'favicons_androidStandalone'        => 'Enable standalone mode?',
	'favicons_androidStandalone_desc'   => 'In this mode, Android Chrome gives a little more "native" style to the opened page.<br>
In particular, it lets you enforce the start URL and screen orientation. It also removes the navigation bar and gives your web site its own tab in the task switcher.',

	'favicons_androidStandalone_startUrl'       => 'Custom start URL',
	'favicons_androidStandalone_startUrl_desc'  => 'Optional. Use this field to override the URL of the bookmarked page.',

	'favicons_androidStandalone_orientation'            => 'Device orientation',
	'favicons_androidStandalone_orientation_desc'       => 'Optional. Forces screen orientation to either portrait or landscape.',
	'favicons_androidStandalone_orientation_default'    => 'Not specified (Recommended)',
	'favicons_androidStandalone_orientation_portrait'   => 'Portrait',
	'favicons_androidStandalone_orientation_landscape'  => 'Landscape',

	# iOS
	'favicons_iosTab'       => 'iOS',
	'favicons_iosHeader'    => 'Android Chrome',

	# Microsoft
	'favicons_msTab'    => 'Microsoft',
	'favicons_msHeader' => 'Windows 8 and 10',

	'favicons_msTileColor'      => 'Tile background color',
	'favicons_msTileColor_desc' => "Preferably, choose one of the above <a href='https://colorlib.com/etc/metro-colors/' target='_blank'>suggested colors for the Windows Metro UI</a>.",

	'favicons_msTileColor_blue'         => 'Blue',
	'favicons_msTileColor_darkBlue'     => 'Dark Blue',
	'favicons_msTileColor_teal'         => 'Teal',
	'favicons_msTileColor_lightPurple'  => 'Light Purple',
	'favicons_msTileColor_darkPurple'   => 'Dark Purple',
	'favicons_msTileColor_darkRed'      => 'Dark Red',
	'favicons_msTileColor_darkOrange'   => 'Dark Orange',
	'favicons_msTileColor_yellow'       => 'Yellow',
	'favicons_msTileColor_green'        => 'Green',

	'favicons_msTileColor_custom'       => 'Custom color',

	/**
	 * [Admin] Manage
	 */
	'favicons_wizard_title' => 'Favicon Generation Wizard',
	'favicons_wizard_run'   => 'Run Wizard',

	# Step 1 - Master
	'favicons_master'       => 'Master Image',
	'favicons_master_hDesc' => 'This is the base image that will be used to generate all of your sites favicons.<br>
For best results, this image should be at least 192 x 192px in size.',

	# Step 2 - Android
	'favicons_android'          => 'Android',
	'favicons_android_hDesc'    => 'Android users can mix their natives apps and web bookmarks on the homescreen. Bookmark links looks like just like native apps.',

	# Step 3 - iOS
	'favicons_ios'      => 'iOS',

	# Step 4 - Android
	'favicons_safari'   => 'Safari',

	# Step 5 - Windows
	'favicons_windows'  => 'Windows',

	# Filters
	'favicons_filter_android'   => 'Android',
	'favicons_filter_ios'       => 'iOS',
	'favicons_filter_safari'    => 'Safari',
	'favicons_filter_windows'   => 'Windows',
);
