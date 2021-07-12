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

use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class EventMessageUpdate extends Packet{

	/** @var Message */
	private $message;

	public function __construct(Message $message){
		parent::__construct();
		$this->message = $message;
	}

	public function getMessage(): Message{
		return $this->message;
	}

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->message
		]);
	}

	public function unserialize($data): void{
		[
			$this->UID,
			$this->message
		] = unserialize($data);
	}
}