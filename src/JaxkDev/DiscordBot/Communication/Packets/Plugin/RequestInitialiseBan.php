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

use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestInitialiseBan extends Packet{

	/** @var Ban */
	private $ban;

	public function __construct(Ban $ban){
		parent::__construct();
		$this->ban = $ban;
	}

	public function getBan(): Ban{
		return $this->ban;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->ban]);
	}

	public function unserialize($data): void{
		[$this->UID, $this->ban] = unserialize($data);
	}
}