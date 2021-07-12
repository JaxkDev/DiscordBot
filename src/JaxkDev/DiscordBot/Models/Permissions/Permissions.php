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

namespace JaxkDev\DiscordBot\Models\Permissions;

abstract class Permissions implements \Serializable{

	const VOICE_PERMISSIONS = [
		"priority_speaker" => 256,
		"stream" => 512,
		"connect" => 1048576,
		"speak" => 2097152,
		"mute_members" => 4194304,
		"deafen_members" => 8388608,
		"move_members" => 16777216,
		"use_vad" => 33554432,
	];

	const TEXT_PERMISSIONS = [
		"add_reactions" => 64,
		"send_messages" => 2048,
		"send_tts_messages" => 4096,
		"manage_messages" => 8192,
		"embed_links" => 16384,
		"attach_files" => 32768,
		"read_message_history" => 65536,
		"mention_everyone" => 131072,
		"use_external_emojis" => 262144,
	];

	const ROLE_PERMISSIONS = [
		"kick_members" => 2,
		"ban_members" => 4,
		"administrator" => 8,
		"manage_guild" => 32,
		"view_audit_log" => 128,
		"view_guild_insights" => 524288,
		"change_nickname" => 67108864,
		"manage_nicknames" => 134217728,
		"manage_emojis" => 1073741824,
	];

	const ALL_PERMISSIONS = [
		"create_instant_invite" => 1,
		"manage_channels" => 16,
		"view_channel" => 1024,
		"manage_roles" => 268435456,
		"manage_webhooks" => 536870912,
	];

	/** @var int */
	private $bitwise = 0;

	/** @var Array<string, bool> */
	private $permissions = [];

	public function __construct(int $bitwise = 0, bool $recalculate = true){
		$this->setBitwise($bitwise, $recalculate);
	}

	public function getBitwise(): int{
		return $this->bitwise;
	}

	public function setBitwise(int $bitwise, bool $recalculate = true): void{
		$this->bitwise = $bitwise;
		if($recalculate) $this->recalculatePermissions();
	}

	/**
	 * Returns all the permissions possible and the current state, or an empty array if not initialised.
	 * @return Array<string, bool>
	 */
	public function getPermissions(): array{
		return $this->permissions;
	}

	public function getPermission(string $permission): ?bool{
		return $this->permissions[$permission] ?? null;
	}

	public function setPermission(string $permission, bool $state = true): Permissions{
		$permission = strtolower($permission);
		$posPermissions = $this->getPossiblePermissions();

		if(!in_array($permission, array_keys($posPermissions))){
			throw new \AssertionError("Invalid permission '{$permission}' for a '".get_parent_class($this)."'");
		}

		if($this->permissions[$permission] === $state) return $this;
		$this->permissions[$permission] = $state;
		$this->bitwise ^= $posPermissions[$permission];
		return $this;
	}

	/**
	 * @internal Using current bitwise recalculate permissions.
	 */
	private function recalculatePermissions(): void{
		$this->permissions = [];
		$possiblePerms = $this->getPossiblePermissions();
		foreach($possiblePerms as $name => $v){
			$this->permissions[$name] = (($this->bitwise & $v) !== 0);
		}
	}

	/**
	 * @return Array<string, int>
	 */
	abstract static function getPossiblePermissions(): array;

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize($this->bitwise);
	}

	public function unserialize($data): void{
		$this->bitwise = unserialize($data);
		$this->recalculatePermissions();
	}
}