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

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Plugin\Utils;

class Invite implements \Serializable{

	/** @var string|null Also used as ID internally, null when creating model. */
	private $code;

	/** @var string */
	private $server_id;

	/** @var string */
	private $channel_id;

	/** @var int How long in seconds from creation time to expire, 0 for never. */
	private $max_age;

	/** @var int|null Timestamp null when creating model. */
	private $created_at;

	/** @var bool */
	private $temporary;

	/** @var int How many times has this invite been used | NOTICE: This does not get updated when used */
	private $uses;

	/** @var int 0 for unlimited uses */
	private $max_uses;

	/** @var string|null Member ID, null when creating model. */
	private $creator;

	public function __construct(string $server_id, string $channel_id, int $max_age, int $max_uses, bool $temporary,
								?string $code = null, ?int $created_at = null, ?string $creator = null, int $uses = 0){
		$this->setServerId($server_id);
		$this->setChannelId($channel_id);
		$this->setMaxAge($max_age);
		$this->setMaxUses($max_uses);
		$this->setTemporary($temporary);
		$this->setCode($code);
		$this->setCreatedAt($created_at);
		$this->setCreator($creator);
		$this->setUses($uses);
	}

	public function getCode(): ?string{
		return $this->code;
	}

	public function setCode(?string $code): void{
		$this->code = $code;
	}

	public function getServerId(): string{
		return $this->server_id;
	}

	public function setServerId(string $server_id): void{
		if(!Utils::validDiscordSnowflake($server_id)){
			throw new \AssertionError("Server ID '$server_id' is invalid.");
		}
		$this->server_id = $server_id;
	}

	public function getChannelId(): string{
		return $this->channel_id;
	}

	public function setChannelId(string $channel_id): void{
		if(!Utils::validDiscordSnowflake($channel_id)){
			throw new \AssertionError("Channel ID '$channel_id' is invalid.");
		}
		$this->channel_id = $channel_id;
	}

	public function getMaxAge(): int{
		return $this->max_age;
	}

	/**  @param int $max_age 0 for eternity. */
	public function setMaxAge(int $max_age): void{
		if($max_age > 604800 or $max_age < 0){
			throw new \AssertionError("Max age '$max_age' is outside bounds 0-604800.");
		}
		$this->max_age = $max_age;
	}

	public function getCreatedAt(): ?int{
		return $this->created_at;
	}

	public function setCreatedAt(?int $created_at): void{
		if($created_at !== null and $created_at > time()){
			throw new \AssertionError("Time travel has been attempted, '$created_at' is in the future !");
		}
		$this->created_at = $created_at;
	}

	public function isTemporary(): bool{
		return $this->temporary;
	}

	public function setTemporary(bool $temporary): void{
		$this->temporary = $temporary;
	}

	public function getUses(): int{
		return $this->uses;
	}

	public function setUses(int $uses): void{
		if($this->max_uses !== 0 and $uses > $this->max_uses){
			throw new \AssertionError("Uses '$uses' is bigger than max uses '$this->max_uses'.");
		}
		$this->uses = $uses;
	}

	public function getMaxUses(): int{
		return $this->max_uses;
	}

	public function setMaxUses(int $max_uses): void{
		if($max_uses < 0 or $max_uses > 100){
			throw new \AssertionError("Max uses '$max_uses' is outside the bounds 0-100.");
		}
		$this->max_uses = $max_uses;
	}

	public function getCreator(): ?string{
		return $this->creator;
	}

	public function setCreator(?string $creator): void{
		$this->creator = $creator;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->code,
			$this->server_id,
			$this->channel_id,
			$this->max_age,
			$this->created_at,
			$this->temporary,
			$this->uses,
			$this->max_uses,
			$this->creator
		]);
	}

	public function unserialize($data): void{
		[
			$this->code,
			$this->server_id,
			$this->channel_id,
			$this->max_age,
			$this->created_at,
			$this->temporary,
			$this->uses,
			$this->max_uses,
			$this->creator
		] = unserialize($data);
	}
}