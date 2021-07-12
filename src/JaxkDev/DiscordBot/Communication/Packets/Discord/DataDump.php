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

use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\Server;
use JaxkDev\DiscordBot\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class DataDump extends Packet{

	/** @var Server[] */
	private $servers = [];

	/** @var ServerChannel[] */
	private $channels = [];

	/** @var Role[] */
	private $roles = [];

	/** @var Invite[] */
	private $invites = [];

	/** @var Ban[] */
	private $bans = [];

	/** @var Member[] */
	private $members = [];

	/** @var User[] */
	private $users = [];

	/** @var null|User */
	private $botUser = null;

	/** @var int */
	private $timestamp;

	/**
	 * @return Server[]
	 */
	public function getServers(): array{
		return $this->servers;
	}

	public function addServer(Server $server): void{
		$this->servers[] = $server;
	}

	/**
	 * @return ServerChannel[]
	 */
	public function getChannels(): array{
		return $this->channels;
	}

	public function addChannel(ServerChannel $channel): void{
		$this->channels[] = $channel;
	}

	/**
	 * @return Role[]
	 */
	public function getRoles(): array{
		return $this->roles;
	}

	public function addRole(Role $role): void{
		$this->roles[] = $role;
	}

	/**
	 * @return Invite[]
	 */
	public function getInvites(): array{
		return $this->invites;
	}

	public function addInvite(Invite $invite): void{
		$this->invites[] = $invite;
	}

	/**
	 * @return Ban[]
	 */
	public function getBans(): array{
		return $this->bans;
	}

	public function addBan(Ban $ban): void{
		$this->bans[] = $ban;
	}

	/**
	 * @return Member[]
	 */
	public function getMembers(): array{
		return $this->members;
	}

	public function addMember(Member $member): void{
		$this->members[] = $member;
	}

	/**
	 * @return User[]
	 */
	public function getUsers(): array{
		return $this->users;
	}

	public function addUser(User $user): void{
		$this->users[] = $user;
	}

	public function getBotUser(): ?User{
		return $this->botUser;
	}

	public function setBotUser(User $bot): void{
		$this->botUser = $bot;
	}

	public function getTimestamp(): int{
		return $this->timestamp;
	}

	public function setTimestamp(int $timestamp): void{
		$this->timestamp = $timestamp;
	}

	public function getSize(): int{
		return sizeof($this->servers)+sizeof($this->channels)+sizeof($this->roles)+sizeof($this->members)
			+sizeof($this->users)+sizeof($this->bans)+sizeof($this->invites);
	}

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->servers,
			$this->channels,
			$this->roles,
			$this->invites,
			$this->bans,
			$this->members,
			$this->users,
			$this->botUser,
			$this->timestamp
		]);
	}

	public function unserialize($data): void{
		[
			$this->UID,
			$this->servers,
			$this->channels,
			$this->roles,
			$this->invites,
			$this->bans,
			$this->members,
			$this->users,
			$this->botUser,
			$this->timestamp
		] = unserialize($data);
	}
}