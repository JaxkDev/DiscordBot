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

class Server implements \Serializable{

	/** @var string */
	private $id;

	/** @var string */
	private $name;

	/** @var string|null */
	private $icon_url;

	/** @var string */
	private $region;

	/** @var string */
	private $owner_id;

	/** @var bool */
	private $large;

	/** @var int */
	private $member_count;

	public function __construct(string $id, string $name, string $region, string $owner_id,
								bool $large, int $member_count, ?string $icon_url = null){
		$this->setId($id);
		$this->setName($name);
		$this->setRegion($region);
		$this->setOwnerId($owner_id);
		$this->setLarge($large);
		$this->setMemberCount($member_count);
		$this->setIconUrl($icon_url);
	}

	public function getId(): string{
		return $this->id;
	}

	public function setId(string $id): void{
		if(!Utils::validDiscordSnowflake($id)){
			throw new \AssertionError("Server ID '$id' is invalid.");
		}
		$this->id = $id;
	}

	public function getName(): string{
		return $this->name;
	}

	public function setName(string $name): void{
		$this->name = $name;
	}

	public function getIconUrl(): ?string{
		return $this->icon_url;
	}

	public function setIconUrl(?string $icon_url): void{
		$this->icon_url = $icon_url;
	}

	public function getRegion(): string{
		return $this->region;
	}

	public function setRegion(string $region): void{
		$this->region = $region;
	}

	public function getOwnerId(): string{
		return $this->owner_id;
	}

	public function setOwnerId(string $owner_id): void{
		if(!Utils::validDiscordSnowflake($owner_id)){
			throw new \AssertionError("Owner ID '$owner_id' is invalid.");
		}
		$this->owner_id = $owner_id;
	}

	public function getCreationTimestamp(): float{
		return Utils::getDiscordSnowflakeTimestamp($this->id);
	}

	public function isLarge(): bool{
		return $this->large;
	}

	public function setLarge(bool $large): void{
		$this->large = $large;
	}

	public function getMemberCount(): int{
		return $this->member_count;
	}

	public function setMemberCount(int $member_count): void{
		$this->member_count = $member_count;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->name,
			$this->icon_url,
			$this->region,
			$this->owner_id,
			$this->large,
			$this->member_count
		]);
	}

	public function unserialize($data): void{
		[
			$this->id,
			$this->name,
			$this->icon_url,
			$this->region,
			$this->owner_id,
			$this->large,
			$this->member_count
		] = unserialize($data);
	}
}