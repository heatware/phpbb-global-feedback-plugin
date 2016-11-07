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
	'HEATWARE_SHOW_BADGE'       	=> 'Show my HeatWare badge',
	'HEATWARE_GLOBAL_ENABLED'		=> 'NOTE: The forum administrators have globally enabled HeatWare feedback. Changing this setting will make no difference.',
	'HEATWARE_USER_SETTING'         => 'User Settings',
));
