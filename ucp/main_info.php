<?php

namespace HeatWare\integration\ucp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\HeatWare\integration\ucp\main_module',
			'title'		=> 'HEATWARE_SETTINGS_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'HEATWARE_TITLE',
					'auth'	=> 'ext_HeatWare/integration',
					'cat'	=> array('HEATWARE_SETTINGS_TITLE')
				),
			),
		);
	}
}
