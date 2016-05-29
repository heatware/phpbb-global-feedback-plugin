<?php

namespace HeatWare\integration\cron\task;

/**
 * Cron task for updating the local feedback cache
 */
class HeatWareSync extends \phpbb\cron\task\base
{
	/**
	 * How often we run the cron (in seconds).
	 * @var int
	 */
	protected $cron_frequency;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	* Constructor
	*
	* @param \phpbb\config\config $config Config object
	*/
	public function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;
		$this->cron_frequency = $this->config['heatware_sync_frequency'];
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		global $db;

		// If globally enabled, check all users. Otherwise only look for users who have enabled it
		if ( $this->config['heatware_global_enable'] )
		{
			$sql = 'SELECT user_id,heatware_id,user_email FROM ' . USERS_TABLE;
		}
		else
		{
			$sql_array = array(
				'heatware_enabled' => '1',
			);
			$sql = 'SELECT user_id,heatware_id,user_email FROM ' . USERS_TABLE . ' WHERE ' . $db->sql_build_array('SELECT', $sql_array);
		}

		$results = $db->sql_query($sql);

		while ( $row = $db->sql_fetchrow($results) )
		{
			// What we need
			$user_id = $row['user_id'];
			$user_email = $row['user_email'];
			$heatware_id = $row['heatware_id'];

			// If the heatware id is currently zero we will perform a lookup to see _if_ we can get a valid one
			if ( $heatware_id == 0 )
			{
				$heatware_id = $this->get_user_id($user_email);

				if ( $heatware_id > 0 )
				{
					$this->update_user_heatware_id($db, $heatware_id, $user_id);
				}
			}

			// Verify we actually have a heatware id. It's not guaranteed that the lookup above returned a valid id!
			if ( $heatware_id > 0 )
			{
				$feedback = $this->get_user_info( $heatware_id );

				$this->update_user_heatware_feedback($db, $feedback, $user_id);
			}
		}

		// Cleanup
		$db->sql_freeresult($results);
		$this->config->set('heatware_sync_last_run', time(), false);
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* For example, a cron task that prunes forums can only run when
	* forum pruning is enabled.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return true;
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['heatware_sync_last_run'] < time() - $this->cron_frequency;
	}

	private function get_user_id( $email )
	{
		$url = $this->config['heatware_api_finduser'] . '?' . 'email=' . $email;
		$response = Requests::get($url, array('X-API-KEY' => $this->config['heatware_api_key']));
		$status = $response->status_code;
		if ( $status == 200 )
		{
			//Request OK
			$body = json_decode( $response->body, true );
			return (int)$body['userId'];
		}
		elseif ( $status == 404 )
		{
			//404 means no user found
			return 0;
		}
		else
		{
			throw new \phpbb\exception\http_exception($status);
		}
	}

	private function get_user_info( $user_id )
	{
		$url = $this->config['heatware_api_getuser'] . '?' . 'userId=' . $user_id;
		$response = Requests::get($url, array('X-API-KEY' => $this->config['heatware_api_key']));
		$status = $response->status_code;
		if ( $status == 200 )
		{
			//Request OK
			$account_data = json_decode( $response->body, true );
			if ( $account_data['profile']['accountStatus'] == 'SUSPENDED' )
			{
				$feedback['status'] = 1;
			}
			else
			{
				$feedback['status'] = 0;
			}
			$feedback['positive'] = (int)$account_data['profile']['feedback']['numPositive'];
			$feedback['negative'] = (int)$account_data['profile']['feedback']['numNegative'];
			$feedback['neutral'] = (int)$account_data['profile']['feedback']['numNeutral'];

			return $feedback;
		}
		else
		{
			throw new \phpbb\exception\http_exception($status);
		}
	}

	private function update_user_heatware_id( $db, $heatware_id, $user_id )
	{
		$sql_array = array(
			'heatware_id' => $heatware_id,
		);

		$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id = ' . (int)$user_id;
		$db->sql_query($sql);
	}

	private function update_user_heatware_feedback( $db, $feedback, $user_id )
	{
		$sql_array = array(
			'heatware_suspended' => $feedback['status'],
			'heatware_positive' => $feedback['positive'],
			'heatware_negative' => $feedback['negative'],
			'heatware_neutral' => $feedback['neutral'],
		);
		$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id = ' . (int)$user_id;
		$db->sql_query($sql);
	}
}
