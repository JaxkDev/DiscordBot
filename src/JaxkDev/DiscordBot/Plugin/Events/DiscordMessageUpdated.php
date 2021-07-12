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

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\Messages\Message;
use pocketmine\event\Cancellable;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a message has been updated.
 *
 * @see DiscordMessageDeleted
 * @see DiscordMessageSent
 */
class DiscordMessageUpdated extends DiscordBotEvent implements Cancellable{

	/** @var Message */
	private $message;

	public function __construct(Plugin $plugin, Message $message){
		parent::__construct($plugin);
		$this->message = $message;
	}

	public function getMessage(): Message{
		return $this->message;
	}
}