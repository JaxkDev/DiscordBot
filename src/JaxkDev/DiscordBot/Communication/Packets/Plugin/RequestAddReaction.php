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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestAddReaction extends Packet{

	/** @var string */
	private $channel_id;

	/** @var string */
	private $message_id;

	/** @var string */
	private $emoji;

	public function __construct(string $channel_id, string $message_id, string $emoji){
		parent::__construct();
		$this->channel_id = $channel_id;
		$this->message_id = $message_id;
		$this->emoji = $emoji;
	}

	public function getChannelId(): string{
		return $this->channel_id;
	}

	public function getMessageId(): string{
		return $this->message_id;
	}

	public function getEmoji(): string{
		return $this->emoji;
	}

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->channel_id,
			$this->message_id,
			$this->emoji
		]);
	}

	public function unserialize($data): void{
		[
			$this->UID,
			$this->channel_id,
			$this->message_id,
			$this->emoji
		] = unserialize($data);
	}
}