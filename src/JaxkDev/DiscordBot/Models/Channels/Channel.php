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

namespace JaxkDev\DiscordBot\Models\Channels;

use JaxkDev\DiscordBot\Plugin\Utils;

abstract class Channel implements \Serializable{

	/** @var string|null */
	protected $id;

	public function __construct(?string $id = null){
		$this->setId($id);
	}

	public function getId(): ?string{
		return $this->id;
	}

	public function setId(?string $id): void{
		if($id !== null and !Utils::validDiscordSnowflake($id)){
			throw new \AssertionError("Channel ID '$id' is invalid.");
		}
		$this->id = $id;
	}
}