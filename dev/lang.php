<?php

$lang = array(
	'__app_favicons'	=> "Advanced Favicons",

	'menu__favicons_favicons'   => 'Favicons',

	# Admin Permissions
	'r__favicons_manage'            => 'Manage Favicons',
	'r__favicons_manage_settings'   => 'Manage favicon settings',
	'r__favicons_create'            => 'Create / generate new favicon sets',

	/**
	 * [Admin] Settings
	 */
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
	'favicons_iosTab'       => 'Apple iOS',
	'favicons_iosHeader'    => 'iOS - Web Clip',

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
	'menu__favicons_favicons_manage'  => 'Manage',

	'favicons_manage_title' => 'Manage Favicons',
	'favicons_wizard_title' => 'Favicon Generation Wizard',
	'favicons_wizard_run'   => 'Setup Wizard',

	'favicons_reset'            => 'Delete Favicons',
	'favicons_reset_success'    => 'All favicons deleted successfully',

	# Table
	'favicons_file' => 'Favicon',
	'favicons_type' => 'Favicon Type',

	'favicons_settings' => 'Favicon Settings',

	'favicons_type_0'   => '<i class="fa fa-image"></i> &nbsp;Original',
	'favicons_type_1'   => '<i class="fa fa-android"></i> &nbsp;Android',
	'favicons_type_2'   => '<i class="fa fa-apple"></i> &nbsp;Apple iOS',
	'favicons_type_3'   => '<i class="fa fa-safari"></i> &nbsp;Safari',
	'favicons_type_4'   => '<i class="fa fa-windows"></i> &nbsp;Windows',
	'favicons_type_9'   => '<i class="fa fa-image"></i> &nbsp;Standard Favicon',

	/**
	 * Wizard
	 */
	# Step 1 - Master
	'favicons_master'       => 'Master Image',
	'favicons_master_hDesc' => 'This is the base image that will be used to generate all of your sites favicons.<br>
For best results, this image should be at least 192 x 192px in size.',

	# Step 2 - Android
	'favicons_android'          => 'Android',
	'favicons_android_hDesc'    => 'Android users can mix their natives apps and web bookmarks on the homescreen. Bookmark links looks like just like native apps.',

	# Step 3 - iOS
	'favicons_ios'              => 'iOS',
	'favicons_iosHeader'        => 'iOS - Web Clip',
	'favicons_ios_hDesc'        => 'iOS can automatically add some visual effects to your icon so that it coordinates with the built-in icons on the Home screen (as it does with application icons).',
	'favicons_iosFancy'         => 'Enable fancy effects?',
	'favicons_iosFancy_desc'    => 'When enabled, the following special effects will be automatically applied to your icon on iOS devices:<br>
<ul>
	<li>Rounded corners</li>
	<li>Drop shadow</li>
	<li>Reflective shine</li>
</ul>',

	# Step 4 - Safari
	'favicons_safari'           => 'Safari',
	'favicons_safariHeader'     => 'Safari Pinned Tab',
	'favicons_safari_hDesc'     => 'Safari 9 for Mac OS X El Capitan implements pinned tabs. This feature relies on an SVG icon. This icon must be monochrome and Safari does the rest.<br>
This is optional and can be skipped if you do not have a SVG image to use. Safari uses the first letter of your domain name to create a default icon in this case.',
	'favicons_safariSvg'        => 'Safari SVG image',
	// 'favicons_safariSvg_desc'   => 'If you do not have an SVG image to use, skip this upload.',

	'favicons_safariTheme'      => 'Safari theme color',
	'favicons_safariTheme_desc' => 'This setting is only relevant if a custom SVG image is being used.',

	# Step 5 - Windows
	'favicons_windows'          => 'Windows',
	'favicons_windowsHeader'    => 'Windows 8 and 10',
	'favicons_windows_hDesc'    => 'Windows 8 users can pin your website on their desktop. Your site appears as a tile, just like a native Windows 8 app.',

	# Step 6 - Rewrites
	'favicons_rewrites'         => 'Rewrites',
	'favicons_rewritesHeader'   => 'Enable rewrites for full browser compatibility',
	'favicons_rewrites_hDesc'   => 'To obtain full browser compatibility, you will need to enable some rewrite rules for the Advanced Favicons application.',
	'favicons_rewrites_pDesc'   => 'While this is optional and can be skipped, it is recommended you take the time to enable these rewrite rules if your server supports it.',
	'favicons_rewrites_step1'   => '<strong>Step 1)</strong><br>First, make sure you have both Friendly URLs and Rewrite URLs enabled <a href="%s" target="_blank">here</a>.',
	'favicons_rewrites_step2'   => '<strong>Step 2)</strong><br>Next, modify the contents of your .htaccess rewrite rules for IPS so that they match the following:',
	'favicons_rewrites_step3'   => '<strong>Step 3)</strong><br>Finally, confirm everything is working by visiting <a href="%s" target="_blank">this link</a>. You should be redirected to your favicon image. If not, double check your rewrite rules and try again.',

	'favicons_rewrites_enable'  => 'Enable Advanced Favicon rewrite rules?',

	# Step 7 - Review
	'favicons_review'       => 'Complete',
	'favicons_reviewHeader' => 'All favicons generated successfully!',
	'favicons_review_hDesc' => 'That\'s it! Your website should now have favicons that are compliant with all devices. Want to test it out? Follow the link below!',

	'favicons_reviewCheck'  => 'Check favicons',
	'favicons_reviewFinish' => 'Complete setup',

	# Filters
	'favicons_filter_android'   => 'Android',
	'favicons_filter_ios'       => 'iOS',
	'favicons_filter_safari'    => 'Safari',
	'favicons_filter_windows'   => 'Windows',

	# Errors
	'favicons_error_runSetupFirst'      => 'You must complete the Setup Wizard before accessing the Favicon Settings page',
	'favicons_error_noFaviconsToDelete' => 'You need to create some favicons before you can delete them!',

	/**
	 * Edit
	 */
	'favicons_edit'     => 'Edit Favicon',
	'favicons_edit_new' => 'New favicon',
);
