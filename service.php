<?php

namespace HeatWare\integration;

class service
{
	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $table_name;

	public function __construct(\phpbb\user $user, $table_name)
	{
		$this->user = $user;
		$this->table_name = $table_name;
	}

	public function get_user()
	{
		var_dump($this->table_name);
		return $this->user;
	}
}
