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

use JaxkDev\DiscordBot\Models\Channels\Channel;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\Server;
use pocketmine\event\Cancellable;
use pocketmine\plugin\Plugin;

/**
 * Emitted when the bot joins a discord server.
 * @see DiscordServerDeleted Emitted when the bot leaves a server
 * @see DiscordServerUpdated Emitted when a server the bot is in has been updated.
 */
class DiscordServerJoined extends DiscordBotEvent implements Cancellable{

	/** @var Server */
	private $server;

	/** @var Role[] */
	private $roles;

	/** @var Channel[] */
	private $channels;

	/** @var Member[] */
	private $members;

	/**
	 * @param Plugin    $plugin
	 * @param Server    $server
	 * @param Role[]    $roles
	 * @param Channel[] $channels
	 * @param Member[]  $members
	 */
	public function __construct(Plugin $plugin, Server $server, array $roles, array $channels, array $members){
		parent::__construct($plugin);
		$this->server = $server;
		$this->roles = $roles;
		$this->channels = $channels;
		$this->members = $members;
	}

	public function getServer(): Server{
		return $this->server;
	}

	/**
	 * @return Role[]
	 */
	public function getRoles(): array{
		return $this->roles;
	}

	/**
	 * @return Channel[]
	 */
	public function getChannels(): array{
		return $this->channels;
	}

	/**
	 * @return Member[]
	 */
	public function getMembers(): array{
		return $this->members;
	}
}