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

class EventRoleDelete extends Packet{

	/** @var string */
	private $role_id;

	public function __construct(string $role_id){
		parent::__construct();
		$this->role_id = $role_id;
	}

	public function getRoleId(): string{
		return $this->role_id;
	}

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->role_id
		]);
	}

	public function unserialize($data): void{
		[
			$this->UID,
			$this->role_id
		] = unserialize($data);
	}
}