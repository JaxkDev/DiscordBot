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

namespace JaxkDev\DiscordBot\Models\Messages;

use JaxkDev\DiscordBot\Models\Embed\Embed;

class Reply extends Message{

	/** @var ?string ID of message replying to. */
	private $referenced_message_id;

	/**
	 * Reply constructor.
	 *
	 * @param string      $channel_id
	 * @param string|null $referenced_message_id
	 * @param string|null $id
	 * @param string      $content
	 * @param Embed|null  $embed
	 * @param string|null $author_id
	 * @param string|null $server_id
	 * @param float|null  $timestamp
	 * @param bool        $everyone_mentioned
	 * @param string[]    $users_mentioned
	 * @param string[]    $roles_mentioned
	 * @param string[]    $channels_mentioned
	 */
	public function __construct(string $channel_id, ?string $referenced_message_id = null, ?string $id = null, string $content = "",
								   ?Embed $embed = null, ?string $author_id = null, ?string $server_id = null, ?float $timestamp = null,
								   bool $everyone_mentioned = false, array $users_mentioned = [], array $roles_mentioned = [],
								   array $channels_mentioned = []){
		parent::__construct($channel_id, $id, $content, $embed, $author_id, $server_id, $timestamp, $everyone_mentioned,
			$users_mentioned, $roles_mentioned, $channels_mentioned);
		$this->setReferencedMessageId($referenced_message_id);
	}

	public function getReferencedMessageId(): ?string{
		return $this->referenced_message_id;
	}

	public function setReferencedMessageId(?string $referenced_message_id): void{
		$this->referenced_message_id = $referenced_message_id;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->content,
			$this->embed,
			$this->author_id,
			$this->channel_id,
			$this->server_id,
			$this->timestamp,
			$this->everyone_mentioned,
			$this->users_mentioned,
			$this->roles_mentioned,
			$this->channels_mentioned,
			$this->referenced_message_id
		]);
	}

	public function unserialize($data): void{
		[
			$this->id,
			$this->content,
			$this->embed,
			$this->author_id,
			$this->channel_id,
			$this->server_id,
			$this->timestamp,
			$this->everyone_mentioned,
			$this->users_mentioned,
			$this->roles_mentioned,
			$this->channels_mentioned,
			$this->referenced_message_id
		] = unserialize($data);
	}
}