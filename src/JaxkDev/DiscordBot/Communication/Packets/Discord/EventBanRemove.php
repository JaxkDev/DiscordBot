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

class EventBanRemove extends Packet{

	/** @var string */
	private $id;

	public function __construct(string $id){
		parent::__construct();
		$this->id = $id;
	}

	public function getId(): string{
		return $this->id;
	}

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->id
		]);
	}

	public function unserialize($data): void{
		[
			$this->UID,
			$this->id
		] = unserialize($data);
	}
}