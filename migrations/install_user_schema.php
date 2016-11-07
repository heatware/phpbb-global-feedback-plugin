<?php

namespace heatware\integration\migrations;

class install_user_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'heatware_id');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'users' => array(
					'heatware_enabled'			=> array('BOOL', 0),
					'heatware_id'				=> array('UINT', 0),
                    'heatware_suspended'		=> array('BOOL', 0),
                    'heatware_positive'			=> array('USINT', 0),
                    'heatware_negative'			=> array('USINT', 0),
                    'heatware_neutral'			=> array('USINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'users' => array(
                    'heatware_enabled',
                    'heatware_id',
                    'heatware_suspended',
                    'heatware_positive',
                    'heatware_negative',
                    'heatware_neutral',
				),
			),
		);
	}
}
