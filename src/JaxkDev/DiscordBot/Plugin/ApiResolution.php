<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin;

class ApiResolution{

	/** @var string[] */
	private $data;

	public function __construct(array $data = []){
		if(sizeof($data) === 0){
			throw new \AssertionError("Expected data for ApiResolution to contain at least a message.");
		}
		$this->data = $data;
	}

	public function getMessage(): string{
		return $this->data[0];
	}

	/**
	 * See API Docs for potential data to be returned per API request.
	 *
	 * @return array
	 */
	public function getData(): array{
		return array_slice($this->data, 1);
	}
}