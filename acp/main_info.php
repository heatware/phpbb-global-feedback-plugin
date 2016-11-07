<?php

namespace heatware\integration\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\heatware\integration\acp\main_module',
			'title'		=> $user->lang('HEATWARE_TITLE'),
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> $user->lang('HEATWARE_INTEGRATION_SETTING'),
					'auth'	=> 'ext_HeatWare/integration && acl_a_board',
					'cat'	=> array($user->lang('HEATWARE_TITLE'))
				),
			),
		);
	}
}
