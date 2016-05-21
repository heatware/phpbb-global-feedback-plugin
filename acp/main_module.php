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

			$config->set('heatware_api_key', $request->variable('heatware_api_key', 0));

			trigger_error($user->lang('HEATWARE_SETTING_SAVED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'HEATWARE_API_KEY'		=> $config['heatware_api_key'],
		));
	}
}
