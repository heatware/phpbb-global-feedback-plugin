<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

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
