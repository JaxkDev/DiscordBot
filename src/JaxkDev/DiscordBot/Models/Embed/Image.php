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

namespace JaxkDev\DiscordBot\Models\Embed;

// https://discord.com/developers/docs/resources/channel#embed-object-embed-image-structure
class Image implements \Serializable{

	/** @var null|string Must be prefixed with `https` */
	private $url;

	/** @var null|int */
	private $width;

	/** @var null|int */
	private $height;

	public function __construct(?string $url = null, ?int $width = null, ?int $height = null){
		$this->setUrl($url);
		$this->setWidth($width);
		$this->setHeight($height);
	}

	public function getUrl(): ?string{
		return $this->url;
	}

	public function setUrl(?string $url): void{
		if($url !== null and strpos($url , "https" ) !== 0){
			throw new \AssertionError("URL '$url' must start with https.");
		}
		$this->url = $url;
	}

	public function getWidth(): ?int{
		return $this->width;
	}

	public function setWidth(?int $width): void{
		$this->width = $width;
	}

	public function getHeight(): ?int{
		return $this->height;
	}

	public function setHeight(?int $height): void{
		$this->height = $height;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->url,
			$this->width,
			$this->height
		]);
	}

	public function unserialize($data): void{
		[
			$this->url,
			$this->width,
			$this->height
		] = unserialize($data);
	}
}