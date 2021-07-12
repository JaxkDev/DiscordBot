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

namespace JaxkDev\DiscordBot\Communication\Packets;

abstract class Packet implements \Serializable{

	// Used for responses.
	/** @var int */
	public static $UID_COUNT = 0;

	/** @var int */
	protected $UID;

	public function __construct(){
		Packet::$UID_COUNT += 2;  //BotThread = Odd, PluginThread = Even. (Keeps them unique, *shrugs*)
		$this->UID = Packet::$UID_COUNT;
	}

	public function getUID(): int{
		return $this->UID;
	}

	public abstract function serialize(): ?string;

	public abstract function unserialize($data): void;
}