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

use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use pocketmine\event\Cancellable;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a channel gets created.
 * @see DiscordChannelDeleted
 * @see DiscordChannelUpdated
 */
class DiscordChannelCreated extends DiscordBotEvent implements Cancellable{

	/** @var ServerChannel */
	private $channel;

	public function __construct(Plugin $plugin, ServerChannel $channel){
		parent::__construct($plugin);
		$this->channel = $channel;
	}

	public function getChannel(): ServerChannel{
		return $this->channel;
	}
}