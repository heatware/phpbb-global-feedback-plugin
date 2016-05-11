<?php

namespace HeatWare\integration\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\HeatWare\integration\acp\main_module',
			'title'		=> 'HeatWare',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'HeatWare',
					'auth'	=> 'ext_HeatWare/integration && acl_a_board',
					'cat'	=> array('HeatWare')
				),
			),
		);
	}
}
