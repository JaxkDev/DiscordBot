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

//This is a big mess in terms of events, any update causes it to be a channel create event.
//So this is not stored/sent in terms of events/dumps its only use is outbound message requests.
use JaxkDev\DiscordBot\Plugin\Utils;

class DmChannel extends Channel{

	//** @var string[] Message ID's */
	//private $pins = [];

	/**
	 * Get recipient of DM channel.
	 *
	 * @see Channel::getId(); DM Channel ID, is recipient user ID.
	 */
	public function getRecipient(): ?string{
		return $this->id;
	}

	/**
	 * Set recipient of DM channel.
	 *
	 * @param string|null $user_id
	 * @see Channel::setId(); DM Channel ID, is recipient user ID.
	 */
	public function setRecipient(?string $user_id): void{
		if($user_id !== null and !Utils::validDiscordSnowflake($user_id)){
			throw new \AssertionError("Recipient user ID '$user_id' is invalid.");
		}
		$this->setId($user_id);
	}

	/*/** @return string[] Message ID's *
	public function getPins(): array{
		return $this->pins;
	}

	/** @param string[] $pins Message ID's *
	public function setPins(array $pins): void{
		$this->pins = $pins;
	}*/

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			//$this->pins
		]);
	}

	public function unserialize($data): void{
		[
			$this->id,
			//$this->pins
		] = unserialize($data);
	}
}