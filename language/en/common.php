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
	'HEATWARE_FEEDBACK_PREFIX'	=> 'HeatWare:',
));

// Logging
$lang = array_merge($lang, array(
	'HEATWARE_HTTP_ERROR'	=> '<strong>Error running HeatWare sync</strong><br/>Error: %1$s<br/>Operation: %2$s<br/>Parameter: %3$s',
));