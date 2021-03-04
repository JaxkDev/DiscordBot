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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class EventChannelDelete extends Packet{

	/** @var string */
	private $channel_id;

	public function getChannelId(): string{
		return $this->channel_id;
	}

	public function setChannelId(string $channel_id): void{
		$this->channel_id = $channel_id;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->channel_id]);
	}

	public function unserialize($serialized): void{
		[$this->UID, $this->channel_id] = unserialize($serialized);
	}
}