<?php

namespace MoneyStat;


use MoneyStat\Tasks\Task;

class Application
{
	protected static $instance;
	protected $db;
	protected $config;

	/**
	 * Application constructor.
	 * @param Config $config
	 * @throws Exceptions\ConfigException
	 */
	protected function __construct(Config $config)
	{
		$this->config = $config;
		$this->db = new Database(
			$this->getConfig('database:host'),
			$this->getConfig('database:name'),
			$this->getConfig('database:login'),
			$this->getConfig('database:password')
		);
	}

	/**
	 * @param string $param
	 * @return mixed
	 * @throws Exceptions\ConfigException
	 */
	public function getConfig($param)
	{
		return $this->config->get($param);
	}

	public static function getInstance()
	{
		if (null == self::$instance) {
			self::$instance = new self(new Config());
		}
		return self::$instance;
	}

	public function getDB()
	{
		return $this->db;
	}

	public function run(Task $task)
	{
		$task->run();
	}
}