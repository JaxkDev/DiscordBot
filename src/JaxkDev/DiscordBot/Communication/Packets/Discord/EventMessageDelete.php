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

class EventMessageDelete extends Packet{

	/** @var string */
	private $message_id;

	public function __construct(string $message_id){
		parent::__construct();
		$this->message_id = $message_id;
	}

	public function getMessageId(): string{
		return $this->message_id;
	}

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->message_id
		]);
	}

	public function unserialize($data): void{
		[
			$this->UID,
			$this->message_id
		] = unserialize($data);
	}
}