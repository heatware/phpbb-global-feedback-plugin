<?php

namespace heatware\integration\ucp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\heatware\integration\ucp\main_module',
			'title'		=> $user->lang('HEATWARE_TITLE'),
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> $user->lang('HEATWARE_USER_SETTING'),
					'auth'	=> 'ext_HeatWare/integration',
					'cat'	=> array($user->lang('HEATWARE_TITLE'))
				),
			),
		);
	}
}
