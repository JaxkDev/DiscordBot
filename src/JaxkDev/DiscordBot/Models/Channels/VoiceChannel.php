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

namespace JaxkDev\DiscordBot\Models\Channels;

use JaxkDev\DiscordBot\Plugin\Utils;

class VoiceChannel extends ServerChannel{

	/** @var int */
	private $bitrate;

	/** @var int The max amount of members that can join. */
	private $member_limit;

	/** @var string[] Members in the channel (ID's only). */
	private $members = [];

	/**
	 * VoiceChannel constructor.
	 *
	 * @param int         $bitrate
	 * @param int         $member_limit
	 * @param string      $name
	 * @param int         $position
	 * @param string      $server_id
	 * @param string[]    $members Array of Member ID's
	 * @param string|null $category_id
	 * @param string|null $id
	 */
	public function __construct(int $bitrate, int $member_limit, string $name, int $position, string $server_id,
								   array $members, ?string $category_id = null, ?string $id = null){
		parent::__construct($name, $position, $server_id, $category_id, $id);
		$this->setBitrate($bitrate);
		$this->setMemberLimit($member_limit);
		$this->setMembers($members);
	}

	public function getBitrate(): int{
		return $this->bitrate;
	}

	public function setBitrate(int $bitrate): void{
		$this->bitrate = $bitrate;
	}

	public function getMemberLimit(): int{
		return $this->member_limit;
	}

	public function setMemberLimit(int $member_limit): void{
		$this->member_limit = $member_limit;
	}

	/** @return string[] Member ID's */
	public function getMembers(): array{
		return $this->members;
	}

	/** @param string[] $members Member ID's */
	public function setMembers(array $members): void{
		foreach($members as $member){
			[$sid, $uid] = explode(".", $member);
			if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
				throw new \AssertionError("Member ID '$member' is invalid.");
			}
		}
		$this->members = $members;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->name,
			$this->position,
			$this->member_permissions,
			$this->role_permissions,
			$this->server_id,
			$this->bitrate,
			$this->member_limit,
			$this->members,
			$this->category_id
		]);
	}

	public function unserialize($data): void{
		[
			$this->id,
			$this->name,
			$this->position,
			$this->member_permissions,
			$this->role_permissions,
			$this->server_id,
			$this->bitrate,
			$this->member_limit,
			$this->members,
			$this->category_id
		] = unserialize($data);
	}
}