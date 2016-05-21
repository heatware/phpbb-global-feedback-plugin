<?php

namespace HeatWare\integration\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['heatware_api_key']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('api_key', '')),
			array('config.add', array('enable_all', '')),
			array('config.add', array('api_url_finduser', 'https://www.heatware.com/api/findUser')),
			array('config.add', array('api_url_getuser', 'https://www.heatware.com/api/user')),

			array('module.add', array(
				'acp',
				'HeatWare Settings',
				array(
					'module_basename'	=> '\HeatWare\integration\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
