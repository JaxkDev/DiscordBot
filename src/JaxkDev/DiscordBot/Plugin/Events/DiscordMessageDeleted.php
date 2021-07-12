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

use pocketmine\event\Cancellable;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a message has been deleted.
 * TODO, Decide properly here and in DiscordEventHandler#onMessageDelete because if message was made/updated before bot
 * started it will only have message id, channel id and server id if it was made/updated after bot started it will have
 * the full message model.
 *
 * @see DiscordMessageUpdated
 * @see DiscordMessageSent
 */
class DiscordMessageDeleted extends DiscordBotEvent implements Cancellable{

	/** @var string */
	private $message_id;

	public function __construct(Plugin $plugin, string $message_id){
		parent::__construct($plugin);
		$this->message_id = $message_id;
	}

	public function getMessageId(): string{
		return $this->message_id;
	}
}