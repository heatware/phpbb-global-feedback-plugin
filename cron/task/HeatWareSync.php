<?php

namespace HeatWare\integration\cron\task;

include('../../vendor/rmccue/requests/library/Requests.php');
Requests::register_autoloader();

/**
 * Cron task for updating the local feedback cache
 */
class HeatWareSync extends \phpbb\cron\task\base
{
	/**
	 * How often we run the cron (in seconds).
	 * @var int
	 */
	protected $cron_frequency = 86400;

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

		//Array with the data to insert
		if ( $this->config['heatware_global_enable'] )
		{
			$sql_array = array(
				'heatware_enabled' => '1',
			);
			$sql = 'SELECT user_id,heatware_id,user_email FROM ' . USERS_TABLE . ' WHERE ' . $db->sql_build_array('SELECT', $sql_array);
		}
		else
		{
			$sql = 'SELECT user_id,heatware_id,user_email FROM ' . USERS_TABLE;
		}

		// Run the query
		$results = $db->sql_query($sql);

		while ( $row = $db->sql_fetchrow($results) )
		{
			// Show we got the result we were looking for
			$user_id = $row['user_id'];
			$heatware_id = $row['heatware_id'];

			// If the heatware id is currently zero we will perform a lookup to see _if_ we can get a valid one
			if ( $heatware_id == 0 )
			{
				$heatware_id = $this->get_user_id($row['user_email']);

				if ( $heatware_id > 0 )
				{
					$sql_array = array(
						'heatware_id' => (int)$heatware_id,
					);

					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id = ' . (int)$user_id;
					$db->sql_query($sql);
				}
			}

			// Verify we actually have a heatware id. It's not guaranteed that the lookup above returned a valid id!
			if ( $heatware_id > 0 )
			{
				$account_data = $this->get_user_info( $heatware_id );
				if ( $account_data['profile']['accountStatus'] == 'SUSPENDED' )
				{
					$suspended = 1;
				}
				else
				{
					$suspended = 0;
				}
				$positive = $account_data['profile']['feedback']['numPositive'];
				$negative = $account_data['profile']['feedback']['numNegative'];
				$neutral = $account_data['profile']['feedback']['numNeutral'];

				$sql_array = array(
					'heatware_suspended' => $suspended,
					'heatware_positive' => (int)$positive,
					'heatware_negative' => (int)$negative,
					'heatware_neutral' => (int)$neutral,
				);
				$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id = ' . (int)$user_id;
				$db->sql_query($sql);
			}
		}

		// Be sure to free the result after a SELECT query
		$db->sql_freeresult($results);

		// Update the cron task run time here if it hasn't
		// already been done by your cron actions.
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
		$url = $this->config['api_url_finduser'] . '?' . 'email=' . $email;
		$response = Requests::get($url, array('X-API-KEY' => $this->config['heatware_api_key']));
		$status = $response->status_code;
		if ( $status == 200 )
		{
			//Request OK
			$body = json_decode( $response->body, true );
			$api_response = json_decode($body['data'], true );
			return (int)$api_response['userId'];
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
		$url = $this->config['api_url_getuser'] . '?' . 'userId=' . $user_id;
		$response = Requests::get($url, array('X-API-KEY' => $this->config['heatware_api_key']));
		$status = $response->status_code;
		if ( $status == 200 )
		{
			//Request OK
			$body = json_decode( $response->body, true );
			return json_decode($body['data'], true );
		}
		else
		{
			throw new \phpbb\exception\http_exception($status);
		}
	}
}
