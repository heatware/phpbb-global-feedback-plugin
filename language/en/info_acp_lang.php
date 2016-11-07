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
	'HEATWARE_FORCE_BADGE'		      => 'Enable badge for all users',
	'HEATWARE_API_KEY'                => 'API Key',
	'HEATWARE_SYNC_FREQUENCY'		  => 'Feedback synchronization frequency',
	'HEATWARE_SYNC_DESC'		      => 'Time in seconds, min: 3600',
	'HEATWARE_GLOBAL_ENABLE'	      => 'Enable feedback badges for all users',
	'HEATWARE_GLOBAL_DESC'		      => 'Checked: Enable feedback badge for all users. Unchecked: Lets users decide for themselves.',
	'HEATWARE_INTEGRATION_SETTING'    => 'Integration Settings',
));
