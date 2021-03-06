<?php

namespace heatware\integration\cron\task;

use Requests;

/**
 * Cron task for updating feedback stored in the users table
 */
class heatware_sync extends \phpbb\cron\task\base
{
	protected $cron_frequency;

	protected $config;

	protected $db;

    protected $user;

	protected $phpbb_log;

	/**
	* Constructor
	*
	* @param \phpbb\config\config $config Config object
	* @param \phpbb\db\driver\driver_interface $db DBAL connection object
	* @param \phpbb\user $user Current user object
	* @param \phpbb\log\log $phpbb_log Log object for writing events
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\log\log $phpbb_log)
	{
		$this->config = $config;
		$this->db = $db;
		$this->cron_frequency = $this->config['heatware_sync_frequency'];
        $this->user = $user;
		$this->phpbb_log = $phpbb_log;
	}

	/**
	* Looks for heatware IDs for any users missing it and then updates all feedback info
	*
	* @return null
	*/
	public function run()
	{
		$limit = 500;

		// For performance reasons we want to break up the results to that we only work on a subset of accounts
		$sql = 'SELECT COUNT(user_id) as total FROM ' . USERS_TABLE;
		$results = $this->db->sql_query($sql);
		$total_users = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($results);
		$loops = (int) floor($total_users / $limit);

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
			$sql = 'SELECT user_id,heatware_id,user_email FROM ' . USERS_TABLE . ' WHERE ' . $this->db->sql_build_array('SELECT', $sql_array);
		}

		for( $index = 0; $index < $loops; $index++)
		{
			$offset = $index * $limit;
			$results = $this->db->sql_query_limit($sql, $limit, $offset);

			while ($row = $this->db->sql_fetchrow($results)) {
				// What we need
				$user_id = $row['user_id'];
				$user_email = $row['user_email'];
				$heatware_id = $row['heatware_id'];

				try {
					// If the heatware id is currently zero we will perform a lookup to see _if_ we can get a valid one
					if ($heatware_id == 0 && !empty($user_email)) {
						$heatware_id = $this->get_user_id($user_email);

						if ($heatware_id > 0) {
							$this->update_user_heatware_id($heatware_id, $user_id);
						}
					}

					// Verify we actually have a heatware id. It's not guaranteed that the lookup above returned a valid id!
					// We want to keep feedback updated even if a user has it currently off. That way if they enable it's up to date.
					if ($heatware_id > 0) {
						$feedback = $this->get_user_info($heatware_id);

						if ($feedback['status'] == 'ok') {
							$this->update_user_heatware_feedback($feedback, $user_id);
						} else {
							// We 404'd so the user ID is no longer valid, reset to zero
							$this->update_user_heatware_id(0, $user_id);
						}
					}
				} catch (\phpbb\exception\runtime_exception $e) {
					break;
				}
			}
			$this->db->sql_freeresult($results);
		}

		$this->config->set('heatware_sync_last_run', time(), false);
	}

	/**
	* Checks for requirements to run cron task
	*
	* @return bool
	*/
	public function is_runnable()
	{
		// With no API key there is no point in running
		if( empty($this->config['heatware_api_key']) )
		{
			return false;
		}

		return true;
	}

	/**
	* Checks timestamps to see if the cron should run
	*
	* @return bool
	*/
	public function should_run()
	{
		if( $this->config['heatware_sync_last_run'] < (time() - $this->cron_frequency) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Obtains HeatWare ID for a provided email
	 *
	 * @param $email
	 *
	 * @return int
	 * @throws \phpbb\exception\http_exception
	 */
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
            $this->user->add_lang_ext('heatware/integration', 'common');
            $message = $this->user->lang('HEATWARE_HTTP_ERROR',$status,'findUser',$email);
			$this->phpbb_log->add('critical',$this->user->data['user_id'], $this->user->ip,'LOG_GENERAL_ERROR',time(),array('',$message) );
			throw new \phpbb\exception\http_exception($status);
		}
	}

	/**
	 * Queries API for all the user info and returns an array of what is needed
	 *
	 * @param $heatware_id
	 *
	 * @return array
	 * @throws \phpbb\exception\http_exception
	 */
	private function get_user_info( $heatware_id )
	{
		$url = $this->config['heatware_api_getuser'] . '?' . 'userId=' . $heatware_id;
		$response = Requests::get($url, array('X-API-KEY' => $this->config['heatware_api_key']));
		$status = $response->status_code;
		if ( $status == 200 )
		{
			//Request OK
			$account_data = json_decode( $response->body, true );
			if ( $account_data['profile']['accountStatus'] == 'SUSPENDED' )
			{
				$feedback['suspended'] = 1;
			}
			else
			{
				$feedback['suspended'] = 0;
			}
			$feedback['positive'] = (int)$account_data['profile']['feedback']['numPositive'];
			$feedback['negative'] = (int)$account_data['profile']['feedback']['numNegative'];
			$feedback['neutral'] = (int)$account_data['profile']['feedback']['numNeutral'];
			$feedback['status'] = 'ok';

			return $feedback;
		}
		elseif ( $status == 404 )
		{
			// If an account was deleted we would get a 404
			$feedback['status'] = 'Not Found';
			return $feedback;
		}
		else
		{
            $this->user->add_lang_ext('heatware/integration', 'common');
            $message = $this->user->lang('HEATWARE_HTTP_ERROR',$status,'user',$heatware_id);
            $this->phpbb_log->add('critical',$this->user->data['user_id'], $this->user->ip,'LOG_GENERAL_ERROR',time(),array('',$message) );
            throw new \phpbb\exception\http_exception($status);
		}
	}

    /**
     * Stores the HeatWare ID for a given user id.
     *
     * @param $heatware_id
     * @param $user_id
     *
     * @return null
     */
	private function update_user_heatware_id( $heatware_id, $user_id )
	{
		$sql_array = array(
			'heatware_id' => $heatware_id,
		);

		$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id = ' . (int)$user_id;
		$this->db->sql_query($sql);
	}

    /**
     * Stores the HeatWare ID for a given user id.
     *
     * @param $feedback array of feedback to store for a given user id
     *
     * @return null
     */
	private function update_user_heatware_feedback( $feedback, $user_id )
	{
		$sql_array = array(
			'heatware_suspended' => $feedback['suspended'],
			'heatware_positive' => $feedback['positive'],
			'heatware_negative' => $feedback['negative'],
			'heatware_neutral' => $feedback['neutral'],
		);
		$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id = ' . (int)$user_id;
		$this->db->sql_query($sql);
	}
}
