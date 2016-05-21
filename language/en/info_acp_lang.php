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
	'HEATWARE_FORCE_BADGE'			=> 'Enable badge for all users',
	'HEATWARE_API_KEY'			=> 'HeatWare API Key',
	'HEATWARE_CRON_SETTING'		=> 'Feedback synchronization frequency',
));
