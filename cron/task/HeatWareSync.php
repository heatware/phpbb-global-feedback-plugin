<?php

namespace HeatWare\integration\cron\task;

use Requests;

/**
 * Cron task for updating feedback stored in the users table
 */
class HeatWareSync extends \phpbb\cron\task\base
{
	protected $cron_frequency;

	protected $config;

	protected $db;

    protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\config\config $config Config object
	* @param \phpbb\db\driver\driver_interface $db DBAL connection object
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user)
	{
		$this->config = $config;
		$this->db = $db;
		$this->cron_frequency = $this->config['heatware_sync_frequency'];
        $this->log = $log;
        $this->user = $user;
	}

	/**
	* Looks for heatware IDs for any users missing it and then updates all feedback info
	*
	* @return null
	*/
	public function run()
	{
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

		$results = $this->db->sql_query($sql);

		while ( $row = $this->db->sql_fetchrow($results) )
		{
			// What we need
			$user_id = $row['user_id'];
			$user_email = $row['user_email'];
			$heatware_id = $row['heatware_id'];

            try {
                // If the heatware id is currently zero we will perform a lookup to see _if_ we can get a valid one
                if ( $heatware_id == 0 && !empty($user_email) ) {
                    $heatware_id = $this->get_user_id($user_email);

                    if ($heatware_id > 0) {
                        $this->update_user_heatware_id($heatware_id, $user_id);
                    }
                }

                // Verify we actually have a heatware id. It's not guaranteed that the lookup above returned a valid id!
                // We want to keep feedback updated even if a user has it currently off. That way if they enable it's up to date.
                if ($heatware_id > 0) {
                    $feedback = $this->get_user_info($heatware_id);

                    $this->update_user_heatware_feedback($feedback, $user_id);
                }
            }
            catch (\phpbb\exception\runtime_exception $e) {
                break;
            }
		}

		// Cleanup
		$this->db->sql_freeresult($results);
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
            $this->user->add_lang_ext('HeatWare/integration', 'common');
            $message = $this->user->lang('HEATWARE_HTTP_ERROR',$status,'findUser',$email);
            add_log('critical','LOG_GENERAL_ERROR','',$message );
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

			return $feedback;
		}
		else
		{
            $this->user->add_lang_ext('HeatWare/integration', 'common');
            $message = $this->user->lang('HEATWARE_HTTP_ERROR',$status,'user',$heatware_id);
            add_log('critical','LOG_GENERAL_ERROR','',$message );
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
