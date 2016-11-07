<?php

namespace heatware\integration\ucp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\heatware\integration\ucp\main_module',
			'title'		=> 'HEATWARE_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'HEATWARE_USER_SETTING',
					'auth'	=> 'ext_heatware/integration',
					'cat'	=> array('HEATWARE_TITLE'),
				),
			),
		);
	}
}
