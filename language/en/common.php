<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'HEATWARE_SETTINGS_TITLE'	=> 'HeatWare Settings',
	'HEATWARE_TITLE'			=> 'HeatWare',
	'HEATWARE_SAVED'			=> 'Settings saved',
));
