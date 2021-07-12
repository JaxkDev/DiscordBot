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

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\CategoryChannel;
use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\Server;
use JaxkDev\DiscordBot\Models\User;

/*
 * Notes:
 * unset() on the removes doesnt destroy the objects until all references are unset....
 */

class Storage{

	/** @var Array<string, Server> */
	private static $serverMap = [];

	/** @var Array<string, ServerChannel> */
	private static $channelMap = [];

	/** @var Array<string, string[]> */
	private static $channelServerMap = [];

	/** @var Array<string, string[]> */
	private static $channelCategoryMap = [];

	/** @var Array<string, string[]> */
	private static $categoryServerMap = [];

	/** @var Array<string, Member> */
	private static $memberMap = [];

	/** @var Array<string, string[]> */
	private static $memberServerMap = [];

	/** @var Array<string, User> */
	private static $userMap = [];

	/** @var Array<string, Role> */
	private static $roleMap = [];

	/** @var Array<string, string[]> */
	private static $roleServerMap = [];

	/** @var Array<string, Ban> */
	private static $banMap = [];

	/** @var Array<string, string[]> */
	private static $banServerMap = [];

	/** @var Array<string, Invite> */
	private static $inviteMap = [];

	/** @var Array<string, string[]> */
	private static $inviteServerMap = [];

	/** @var null|User */
	private static $botUser = null;

	/** @var int */
	private static $timestamp = 0;

	public static function getServer(string $id): ?Server{
		return self::$serverMap[$id] ?? null;
	}

	public static function addServer(Server $server): void{
		if(isset(self::$serverMap[($id = $server->getId())])) return; //Already added.
		self::$serverMap[$id] = $server;
		self::$channelServerMap[$id] = [];
		self::$categoryServerMap[$id] = [];
		self::$memberServerMap[$id] = [];
		self::$roleServerMap[$id] = [];
		self::$inviteServerMap[$id] = [];
		self::$banServerMap[$id] = [];
	}

	public static function updateServer(Server $server): void{
		if(!isset(self::$serverMap[$server->getId()])){
			self::addServer($server);
		}else{
			self::$serverMap[$server->getId()] = $server;
		}
	}

	/**
	 * NOTICE, Removes all linked members,channels and roles.
	 * @param string $serverId
	 */
	public static function removeServer(string $serverId): void{
		if(!isset(self::$serverMap[$serverId])) return; //Was never added or already deleted.
		unset(self::$serverMap[$serverId]);
		//Remove servers channels.
		foreach(self::$channelServerMap[$serverId] as $cid){
			unset(self::$channelMap[$cid]);
		}
		unset(self::$channelServerMap[$serverId]);
		//Remove servers category's.
		foreach(self::$categoryServerMap[$serverId] as $cid){
			unset(self::$channelMap[$cid]); //Category's are channels.
		}
		unset(self::$channelServerMap[$serverId]);
		//Remove servers members.
		foreach(self::$memberServerMap[$serverId] as $mid){
			unset(self::$memberMap[$mid]);
		}
		unset(self::$memberServerMap[$serverId]);
		//Remove servers roles.
		foreach(self::$roleServerMap[$serverId] as $rid){
			unset(self::$roleMap[$rid]);
		}
		unset(self::$roleServerMap[$serverId]);
		//Remove servers invites.
		foreach(self::$inviteServerMap[$serverId] as $iid){
			unset(self::$inviteMap[$iid]);
		}
		unset(self::$inviteServerMap[$serverId]);
		//Remove servers bans.
		foreach(self::$banServerMap[$serverId] as $bid){
			unset(self::$banMap[$bid]);
		}
		unset(self::$banServerMap[$serverId]);
	}

	public static function getChannel(string $id): ?ServerChannel{
		return self::$channelMap[$id] ?? null;
	}

	/**
	 * @param string $serverId
	 * @return ServerChannel[]
	 */
	public static function getChannelsByServer(string $serverId): array{
		$channels = [];
		foreach((self::$channelServerMap[$serverId] ?? []) as $id){
			$c = self::getChannel($id);
			if($c !== null) $channels[] = $c;
		}
		return $channels;
	}

	/**
	 * @param string $categoryId
	 * @return ServerChannel[]
	 */
	public static function getChannelsByCategory(string $categoryId): array{
		$channels = [];
		foreach((self::$channelCategoryMap[$categoryId] ?? []) as $id){
			$c = self::getChannel($id);
			if($c !== null){
				if($c instanceof CategoryChannel){
					throw new \AssertionError("Channel '".$c->getId()."' error 0x0002 (Report this on github if you see this)");
				}else{
					$channels[] = $c;
				}
			}
		}
		return $channels;
	}

	/**
	 * @param string $serverId
	 * @return CategoryChannel[]
	 */
	public static function getCategoriesByServer(string $serverId): array{
		$channels = [];
		foreach((self::$categoryServerMap[$serverId] ?? []) as $id){
			$c = self::getChannel($id);
			if($c !== null){
				if(!$c instanceof CategoryChannel){
					throw new \AssertionError("Channel '".$c->getId()."' error 0x0001 (Report this on github if you see this)");
				}else{
					$channels[] = $c;
				}
			}
		}
		return $channels;
	}

	public static function addChannel(ServerChannel $channel): void{
		if($channel->getId() === null){
			throw new \AssertionError("Failed to add channel to storage, ID not found.");
		}
		if(isset(self::$channelMap[$channel->getId()])) return;
		if($channel instanceof CategoryChannel){
			self::$categoryServerMap[$channel->getServerId()][] = $channel->getId();
		}else{
			self::$channelServerMap[$channel->getServerId()][] = $channel->getId();
			self::$channelCategoryMap[$channel->getCategoryId()][] = $channel->getId();
		}
		self::$channelMap[$channel->getId()] = $channel;
	}

	public static function updateChannel(ServerChannel $channel): void{
		if($channel->getId() === null){
			throw new \AssertionError("Failed to update channel in storage, ID not found.");
		}
		if(!isset(self::$channelMap[$channel->getId()])){
			self::addChannel($channel);
		}else{
			self::$channelMap[$channel->getId()] = $channel;
		}
	}

	public static function removeChannel(string $channelId): void{
		$channel = self::getChannel($channelId);
		if($channel === null) return; //Already deleted or not added.
		unset(self::$channelMap[$channelId]);
		$serverId = $channel->getServerId();
		if($channel instanceof CategoryChannel){
			if(isset(self::$channelCategoryMap[$channelId])) unset(self::$channelCategoryMap[$channelId]);
			$i = array_search($channelId, self::$categoryServerMap[$serverId], true);
			if($i === false || is_string($i)) return; //Not in this servers category map.
			array_splice(self::$categoryServerMap[$serverId], $i, 1);
		}elseif($channel instanceof ServerChannel){
			$i = array_search($channelId, self::$channelServerMap[$serverId], true);
			if($i === false || is_string($i)) return; //Not in this servers channel map.
			array_splice(self::$channelServerMap[$serverId], $i, 1);
		}
	}

	public static function getMember(string $id): ?Member{
		return self::$memberMap[$id] ?? null;
	}

	/**
	 * @param string $serverId
	 * @return Member[]
	 */
	public static function getMembersByServer(string $serverId): array{
		$members = [];
		foreach((self::$memberServerMap[$serverId] ?? []) as $id){
			$m = self::getMember($id);
			if($m !== null) $members[] = $m;
		}
		return $members;
	}

	public static function addMember(Member $member): void{
		if(isset(self::$memberMap[$member->getId()])) return;
		self::$memberServerMap[$member->getServerId()][] = $member->getId();
		self::$memberMap[$member->getId()] = $member;
	}

	public static function updateMember(Member $member): void{
		if(!isset(self::$memberMap[$member->getId()])){
			self::addMember($member);
		}else{
			self::$memberMap[$member->getId()] = $member;
		}
	}

	public static function removeMember(string $memberID): void{
		$member = self::getMember($memberID);
		if($member === null) return; //Already deleted or not added.
		$serverId = $member->getServerId();
		unset(self::$memberMap[$memberID]);
		$i = array_search($memberID, self::$memberServerMap[$serverId], true);
		if($i === false || is_string($i)) return; //Not in this servers member map.
		array_splice(self::$memberServerMap[$serverId], $i, 1);
	}

	public static function getUser(string $id): ?User{
		return self::$userMap[$id] ?? null;
	}

	public static function addUser(User $user): void{
		self::$userMap[$user->getId()] = $user;
	}

	/**
	 * Same function as addUser because no links are kept for users.
	 * @param User $user
	 */
	public static function updateUser(User $user): void{
		//No links can overwrite.
		self::addUser($user);
	}

	public static function removeUser(string $userId): void{
		unset(self::$userMap[$userId]);
	}

	public static function getRole(string $id): ?Role{
		return self::$roleMap[$id] ?? null;
	}

	/**
	 * @param string $serverId
	 * @return Role[]
	 */
	public static function getRolesByServer(string $serverId): array{
		$roles = [];
		foreach((self::$roleServerMap[$serverId] ?? []) as $id){
			$r = self::getRole($id);
			if($r !== null){
				$roles[] = $r;
			}
		}
		return $roles;
	}

	public static function addRole(Role $role): void{
		if($role->getId() === null){
			throw new \AssertionError("Failed to add role to storage, ID not found.");
		}
		if(isset(self::$roleMap[$role->getId()])) return;
		self::$roleServerMap[$role->getServerId()][] = $role->getId();
		self::$roleMap[$role->getId()] = $role;
	}

	public static function updateRole(Role $role): void{
		if($role->getId() === null){
			throw new \AssertionError("Failed to update role in storage, ID not found.");
		}
		if(!isset(self::$roleMap[$role->getId()])){
			self::addRole($role);
		}else{
			self::$roleMap[$role->getId()] = $role;
		}
	}

	public static function removeRole(string $roleID): void{
		$role = self::getRole($roleID);
		if($role === null) return; //Already deleted or not added.
		$serverId = $role->getServerId();
		unset(self::$roleMap[$roleID]);
		$i = array_search($roleID, self::$roleServerMap[$serverId], true);
		if($i === false || is_string($i)) return; //Not in this servers role map.
		array_splice(self::$roleServerMap[$serverId], $i, 1);
	}

	public static function getBan(string $id): ?Ban{
		return self::$banMap[$id] ?? null;
	}

	/**
	 * @param string $server_id
	 * @return Ban[]
	 */
	public static function getServerBans(string $server_id): array{
		$bans = [];
		foreach((self::$banServerMap[$server_id]??[]) as $member){
			$b = self::getBan($member);
			if($b !== null) $bans[] = $b;
		}
		return $bans;
	}

	public static function addBan(Ban $ban): void{
		if(isset(self::$banMap[$ban->getId()])) return;
		self::$banMap[$ban->getId()] = $ban;
		self::$banServerMap[$ban->getServerId()][] = $ban->getId();
	}

	public static function removeBan(string $id): void{
		$ban = self::getBan($id);
		if($ban === null) return; //Already deleted or not added.
		$serverId = $ban->getServerId();
		unset(self::$banMap[$id]);
		$i = array_search($id, self::$banServerMap[$serverId], true);
		if($i === false || is_string($i)) return; //Not in this servers ban map.
		array_splice(self::$banServerMap[$serverId], $i, 1);
	}

	public static function getInvite(string $code): ?Invite{
		return self::$inviteMap[$code] ?? null;
	}

	/**
	 * @param string $serverId
	 * @return Invite[]
	 */
	public static function getInvitesByServer(string $serverId): array{
		$invites = [];
		foreach((self::$inviteServerMap[$serverId] ?? []) as $id){
			$i = self::getInvite($id);
			if($i !== null) $invites[] = $i;
		}
		return $invites;
	}

	public static function addInvite(Invite $invite): void{
		if($invite->getCode() === null){
			throw new \AssertionError("Failed to add invite to storage, Code not found.");
		}
		if(isset(self::$inviteMap[$invite->getCode()])) return;
		self::$inviteServerMap[$invite->getServerId()][] = $invite->getCode();
		self::$inviteMap[$invite->getCode()] = $invite;
	}

	public static function updateInvite(Invite $invite): void{
		if($invite->getCode() === null){
			throw new \AssertionError("Failed to update invite in storage, Code not found.");
		}
		if(!isset(self::$inviteMap[$invite->getCode()])){
			self::addinvite($invite);
		}else{
			self::$inviteMap[$invite->getCode()] = $invite;
		}
	}

	public static function removeInvite(string $code): void{
		$invite = self::getinvite($code);
		if($invite === null) return; //Already deleted or not added.
		$serverId = $invite->getServerId();
		unset(self::$inviteMap[$code]);
		$i = array_search($code, self::$inviteServerMap[$serverId], true);
		if($i === false || is_string($i)) return; //Not in this servers invite map.
		array_splice(self::$inviteServerMap[$serverId], $i, 1);
	}

	public static function getBotUser(): ?User{
		return self::$botUser;
	}

	public static function setBotUser(User $user): void{
		self::$botUser = $user;
	}

	public static function getBotMemberByServer(string $serverId): ?Member{
		$u = self::getBotUser();
		if($u === null) return null;
		return self::getMember("{$serverId}.{$u->getId()}");
	}

	public static function getTimestamp(): int{
		return self::$timestamp;
	}

	public static function setTimestamp(int $timestamp): void{
		self::$timestamp = $timestamp;
	}
}