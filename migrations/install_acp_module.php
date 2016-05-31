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
			array('config.add', array('heatware_api_key', '')),
			array('config.add', array('heatware_sync_frequency', 86400)),
			array('config.add', array('heatware_global_enable', 1)),
			array('config.add', array('heatware_api_finduser', 'https://www.heatware.com/api/findUser')),
			array('config.add', array('heatware_api_getuser', 'https://www.heatware.com/api/user')),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'HeatWare'
			)),
			array('module.add', array(
				'acp',
				'HeatWare',
				array(
					'module_basename'	=> '\HeatWare\integration\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
