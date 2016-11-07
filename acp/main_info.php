<?php

namespace heatware\integration\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\heatware\integration\acp\main_module',
			'title'		=> 'HEATWARE_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'HEATWARE_INTEGRATION_SETTING',
					'auth'	=> 'ext_heatware/integration && acl_a_board',
					'cat'	=> array('HEATWARE_TITLE'),
				),
			),
		);
	}
}
