<?php

namespace HeatWare\integration\cron\task;

use Symfony\Component\HttpFoundation\Request;

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
		if( intval($this->config['heatware_sync_frequency']) > 0 )
		{
			$this->cron_frequency = intval($this->config['heatware_api_key']);
		}
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		// Run your cron actions here...
        /*$request = Request::create(
            $this->config['api_url_finduser'],
            'GET',
            array('name' => 'Fabien')
        );
        $request->headers->set('X-API-KEY', $this->config['heatware_api_key']);*/
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
}
