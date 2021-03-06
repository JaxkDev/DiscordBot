<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Bot\Handlers;

use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use JaxkDev\DiscordBot\Bot\Client;

class DiscordEventHandler {
	/**
	 * @var Client
	 */
	private $client;

	public function __construct(Client $client){
		$this->client = $client;
	}

	public function registerEvents(): void{
		$discord = $this->client->getDiscordClient();
		$discord->on('MESSAGE_CREATE', array($this, 'onMessage'));
		$discord->on('GUILD_MEMBER_ADD', array($this, 'onMemberJoin'));
		$discord->on('GUILD_MEMBER_REMOVE', array($this, 'onMemberLeave'));
	}

	public function onMessage(Message $message, Discord $discord){
		if($message->author instanceof Member ? $message->author->user->bot : $message->author->bot) return;

		// Other types of messages not used right now.
		if($message->type !== Message::TYPE_NORMAL) return;
		if($message->channel->type !== Channel::TYPE_TEXT) return;
		if($message->content === "") return; //Images/Files.

		// Eg webhooks ?
		if(!$message->author instanceof Member) return;

		// Clean mentions:
		// Channels:
		$message->content = preg_replace_callback("/<#[0-9]+>/", function($d){
			$id = substr($d[0], 2, 18); //Fixed format afaik.
			return "#".$this->client->getDiscordClient()->getChannel($id)->name;
		}, $message->content);

		// Users:
		$message->content = preg_replace_callback("/<@!?[0-9]+>/", function($d){
			$id = substr($d[0], ($d[0][2] === "!" ? 3 : 2), 18);
			$user = $this->client->getDiscordClient()->users->get("id", $id);
			return "@".$user->username."#".$user->discriminator;
		}, $message->content);

		// Roles:
		$message->content = preg_replace_callback("/<@&[0-9]+>/", function($d) use($message){
			$id = substr($d[0], ($d[0][2] === "&" ? 3 : 2), 18);
			return "@".$message->author->guild->roles->get("id", $id)->name;
		}, $message->content);

		$this->client->getPluginCommunicationHandler()->sendMessageSentEvent(
			$message->author->guild->id,
			$message->author->guild->name,
			$message->author->user->id,
			$message->author->user->discriminator,
			$message->author->user->username,
			$message->channel->id,
			$message->channel->name,
			$message->content,
			$message->timestamp->getTimestamp()
		);
	}

	public function onMemberJoin(Member $member, Discord $discord){
		$this->client->getPluginCommunicationHandler()->sendMemberJoinEvent(
			$member->guild->id,
			$member->guild->name,
			$member->id,
			$member->discriminator,
			$member->username,
			$member->joined_at->getTimestamp()
		);
	}

	public function onMemberLeave(Member $member, Discord $discord){
		$this->client->getPluginCommunicationHandler()->sendMemberLeaveEvent(
			$member->guild->id,
			$member->guild->name,
			$member->id,
			$member->discriminator,
			$member->username,
			time()
		);
	}
}