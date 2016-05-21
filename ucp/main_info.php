<?php

namespace HeatWare\integration\ucp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\HeatWare\integration\ucp\main_module',
			'title'		=> 'HeatWare',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'User Settings',
					'auth'	=> 'ext_HeatWare/integration',
					'cat'	=> array('HeatWare')
				),
			),
		);
	}
}
