<?php
/**
 * Created by mr.Umnik.
 */

namespace MoneyStat\Controller;


use Psr\Container\ContainerInterface;

class CheckEmail
{
	protected $container;

	public function __construct(ContainerInterface $c)
	{
		$this->container = $c;
	}

	public function __invoke($request, $response, $args)
	{
		// @todo checking email
		return $response;
	}
}