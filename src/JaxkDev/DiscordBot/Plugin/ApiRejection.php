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

class ApiRejection extends \Exception{

	/** @var string[] */
	private $data;

	public function __construct(string $message, array $data = []){
		parent::__construct($message);
		$this->data = $data;
	}

	public function getOriginalMessage(): ?string{
		return $this->data[0]??null;
	}

	public function getOriginalTrace(): ?string{
		return $this->data[1]??null;
	}
}