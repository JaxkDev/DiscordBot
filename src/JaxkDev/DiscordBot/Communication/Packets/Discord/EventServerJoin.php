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

use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\Server;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class EventServerJoin extends Packet{

	/** @var Server */
	private $server;

	/** @var ServerChannel[] */
	private $channels;

	/** @var Member[] */
	private $members;

	/** @var Role[] */
	private $roles;

	/**
	 * EventServerJoin constructor.
	 *
	 * @param Server          $server
	 * @param ServerChannel[] $channels
	 * @param Member[]        $members
	 * @param Role[]          $roles
	 */
	public function __construct(Server $server, array $channels, array $members, array $roles){
		parent::__construct();
		$this->server = $server;
		$this->channels = $channels;
		$this->members = $members;
		$this->roles = $roles;
	}

	public function getServer(): Server{
		return $this->server;
	}

	/** @return ServerChannel[] */
	public function getChannels(): array{
		return $this->channels;
	}

	/** @return Role[] */
	public function getRoles(): array{
		return $this->roles;
	}

	/** @return Member[] */
	public function getMembers(): array{
		return $this->members;
	}

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->server,
			$this->roles,
			$this->channels,
			$this->members
		]);
	}

	public function unserialize($data): void{
		[
			$this->UID,
			$this->server,
			$this->roles,
			$this->channels,
			$this->members
		] = unserialize($data);
	}
}