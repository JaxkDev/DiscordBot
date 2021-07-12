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

use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;
use JaxkDev\DiscordBot\Plugin\Utils;

class Role implements \Serializable{

	/** @var null|string */
	private $id;

	/** @var string */
	private $name;

	/** @var RolePermissions */
	private $permissions;

	/** @var int */
	private $colour;

	/** @var bool Is role hoisted on member list. */
	private $hoisted;

	/** @var int */
	private $hoisted_position;

	/** @var bool */
	private $mentionable;

	/** @var string */
	private $server_id;

	public function __construct(string $name, int $colour, bool $hoisted, int $hoisted_position, bool $mentionable,
								string $server_id, RolePermissions $permissions = null, ?string $id = null){
		$this->setName($name);
		$this->setColour($colour);
		$this->setHoisted($hoisted);
		$this->setHoistedPosition($hoisted_position);
		$this->setMentionable($mentionable);
		$this->setServerId($server_id);
		$this->setPermissions($permissions??new RolePermissions(0));
		$this->setId($id);
	}

	public function getId(): ?string{
		return $this->id;
	}

	public function setId(?string $id): void{
		if($id !== null and !Utils::validDiscordSnowflake($id)){
			throw new \AssertionError("Role ID '$id' is invalid.");
		}
		$this->id = $id;
	}

	public function getName(): string{
		return $this->name;
	}

	public function setName(string $name): void{
		$this->name = $name;
	}

	public function getPermissions(): RolePermissions{
		return $this->permissions;
	}

	public function setPermissions(RolePermissions $permissions): void{
		$this->permissions = $permissions;
	}

	public function getColour(): int{
		return $this->colour;
	}

	/**
	 * @param int $colour Hex [0x000000 - 0xFFFFFF]
	 */
	public function setColour(int $colour): void{
		if($colour < 0 or $colour > 0xFFFFFF){
			throw new \AssertionError("Colour '$colour' is outside the bounds 0x000000-0xFFFFFF.");
		}
		$this->colour = $colour;
	}

	public function isHoisted(): bool{
		return $this->hoisted;
	}

	public function setHoisted(bool $hoisted): void{
		$this->hoisted = $hoisted;
	}

	public function getHoistedPosition(): int{
		return $this->hoisted_position;
	}

	public function setHoistedPosition(int $hoisted_position): void{
		$this->hoisted_position = $hoisted_position;
	}

	public function isMentionable(): bool{
		return $this->mentionable;
	}

	public function setMentionable(bool $mentionable): void{
		$this->mentionable = $mentionable;
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

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->name,
			$this->colour,
			$this->permissions,
			$this->mentionable,
			$this->hoisted,
			$this->hoisted_position,
			$this->server_id
		]);
	}

	public function unserialize($data): void{
		[
			$this->id,
			$this->name,
			$this->colour,
			$this->permissions,
			$this->mentionable,
			$this->hoisted,
			$this->hoisted_position,
			$this->server_id
		] = unserialize($data);
	}
}