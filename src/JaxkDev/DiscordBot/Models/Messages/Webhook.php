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

class Webhook extends Message{

	/** @var Embed[] Max 10 in webhook message. */
	private $embeds = [];

	/** @var String */
	private $webhook_id;

	/**
	 * Webhook constructor.
	 *
	 * @param string      $channel_id
	 * @param string      $webhook_id
	 * @param Embed[]     $embeds
	 * @param string|null $id
	 * @param string      $content
	 * @param string|null $author_id
	 * @param string|null $server_id
	 * @param float|null  $timestamp
	 * @param bool        $everyone_mentioned
	 * @param string[]    $users_mentioned
	 * @param string[]    $roles_mentioned
	 * @param string[]    $channels_mentioned
	 */
	public function __construct(string $channel_id, string $webhook_id, array $embeds = [], ?string $id = null, string $content = "",
								   ?string $author_id = null, ?string $server_id = null, ?float $timestamp = null,
								   bool $everyone_mentioned = false, array $users_mentioned = [], array $roles_mentioned = [],
								   array $channels_mentioned = []){
		parent::__construct($channel_id, $id, $content, null, $author_id, $server_id, $timestamp, $everyone_mentioned,
			$users_mentioned, $roles_mentioned, $channels_mentioned);
		$this->setWebhookId($webhook_id);
		$this->setEmbeds($embeds);
	}

	//Hmm...
	public function getEmbed(): ?Embed{
		throw new \AssertionError("Webhook messages must use getEmbeds()");
	}

	public function setEmbed(?Embed $embed): void{
		if($embed !== null) throw new \AssertionError("Webhook messages must use setEmbeds()");
	}

	/** @return Embed[] */
	public function getEmbeds(): array{
		return $this->embeds;
	}

	/** @param Embed[] $embeds */
	public function setEmbeds(array $embeds): void{
		if(sizeof($embeds) > 10){
			throw new \AssertionError("Webhook messages are limited to 10 embeds.");
		}
		$this->embeds = $embeds;
	}

	public function getWebhookId(): string{
		return $this->webhook_id;
	}

	public function setWebhookId(string $webhook_id): void{
		$this->webhook_id = $webhook_id;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->content,
			$this->embeds,
			$this->author_id,
			$this->channel_id,
			$this->server_id,
			$this->timestamp,
			$this->everyone_mentioned,
			$this->users_mentioned,
			$this->roles_mentioned,
			$this->channels_mentioned,
			$this->webhook_id
		]);
	}

	public function unserialize($data): void{
		[
			$this->id,
			$this->content,
			$this->embeds,
			$this->author_id,
			$this->channel_id,
			$this->server_id,
			$this->timestamp,
			$this->everyone_mentioned,
			$this->users_mentioned,
			$this->roles_mentioned,
			$this->channels_mentioned,
			$this->webhook_id
		] = unserialize($data);
	}
}