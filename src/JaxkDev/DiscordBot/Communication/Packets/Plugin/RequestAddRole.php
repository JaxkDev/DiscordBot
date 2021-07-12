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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestAddRole extends Packet{

	/** @var string */
	private $server_id;

	/** @var string */
	private $user_id;

	/** @var string */
	private $role_id;

	public function __construct(string $server_id, string $user_id, string $role_id){
		parent::__construct();
		$this->server_id = $server_id;
		$this->user_id = $user_id;
		$this->role_id = $role_id;
	}

	public function getServerId(): string{
		return $this->server_id;
	}

	public function getUserId(): string{
		return $this->user_id;
	}

	public function getRoleId(): string{
		return $this->role_id;
	}

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->server_id,
			$this->user_id,
			$this->role_id
		]);
	}

	public function unserialize($data): void{
		[
			$this->UID,
			$this->server_id,
			$this->user_id,
			$this->role_id
		] = unserialize($data);
	}
}