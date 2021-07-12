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
use JaxkDev\DiscordBot\Models\Invite;

class RequestInitialiseInvite extends Packet{

	/** @var Invite */
	private $invite;

	public function __construct(Invite $invite){
		parent::__construct();
		$this->invite = $invite;
	}

	public function getInvite(): Invite{
		return $this->invite;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->invite]);
	}

	public function unserialize($data): void{
		[$this->UID, $this->invite] = unserialize($data);
	}
}