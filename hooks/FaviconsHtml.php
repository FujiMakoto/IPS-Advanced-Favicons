//<?php

class favicons_hook_FaviconsHtml extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'globalTemplate' => 
  array (
    0 => 
    array (
      'selector' => 'html > head > title',
      'type' => 'add_after',
      'content' => '{{if settings.favicons_setUpComplete}}
	{{$faviconsHtml = \IPS\favicons\Favicon::html();}}
	{{if $faviconsHtml}}
		{$faviconsHtml|raw}
	{{endif}}
{{endif}}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */














}