<?php

namespace HeatWare\integration\acp;

class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang_ext('HeatWare/integration', 'common');
		$this->tpl_name = 'acp_body';
		$this->page_title = $user->lang('HEATWARE_SETTINGS_TITLE');
		add_form_key('HeatWare/integration');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('HeatWare/integration'))
			{
				trigger_error('FORM_INVALID');
			}

            // Don't allow sync times of less than 1 hour
            $sync_frequency = $request->variable('heatware_sync_frequency', $config['heatware_sync_frequency']);
            if( $sync_frequency < 3600 )
            {
                $sync_frequency = 3600;
            }

			$config->set('heatware_api_key', $request->variable('heatware_api_key', $config['heatware_api_key']));
            $config->set('heatware_sync_frequency', $sync_frequency );
            $config->set('heatware_global_enable', $request->variable('heatware_global_enable', 0));

			trigger_error($user->lang('HEATWARE_SAVED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'                  => $this->u_action,
            'L_HEATWARE_API_KEY'        => $user->lang('HEATWARE_API_KEY_SETTING'),
			'S_HEATWARE_API_KEY'		=> $config['heatware_api_key'],
            'L_HEATWARE_SYNC_FREQUENCY' => $user->lang('HEATWARE_SYNC_SETTING'),
			'S_HEATWARE_SYNC_FREQUENCY' => $config['heatware_sync_frequency'],
            'L_HEATWARE_GLOBAL_ENABLE' => $user->lang('HEATWARE_GLOBAL_SETTING'),
            'S_HEATWARE_GLOBAL_ENABLE' => $config['heatware_global_enable'],
		));
	}
}
