<?php

namespace HeatWare\integration\ucp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\HeatWare\integration\ucp\main_module',
			'title'		=> 'UCP_DEMO_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'UCP_DEMO',
					'auth'	=> 'ext_HeatWare/integration',
					'cat'	=> array('UCP_DEMO_TITLE')
				),
			),
		);
	}
}
