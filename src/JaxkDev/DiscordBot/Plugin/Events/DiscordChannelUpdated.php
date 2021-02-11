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

use JaxkDev\DiscordBot\Communication\Models\Channel;
use pocketmine\event\Cancellable;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a channel gets updated.
 * @see DiscordChannelDeleted
 * @see DiscordChannelCreated
 */
class DiscordChannelUpdated extends DiscordBotEvent implements Cancellable{

	/** @var Channel */
	private $channel;

	public function __construct(Plugin $plugin, Channel $channel){
		parent::__construct($plugin);
		$this->channel = $channel;
	}

	public function getChannel(): Channel{
		return $this->channel;
	}

}