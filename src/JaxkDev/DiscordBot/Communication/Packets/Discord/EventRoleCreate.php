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

use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class EventRoleCreate extends Packet{

	/** @var Role */
	private $role;

	public function __construct(Role $role){
		parent::__construct();
		$this->role = $role;
	}

	public function getRole(): Role{
		return $this->role;
	}

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->role
		]);
	}

	public function unserialize($data): void{
		[
			$this->UID,
			$this->role
		] = unserialize($data);
	}
}