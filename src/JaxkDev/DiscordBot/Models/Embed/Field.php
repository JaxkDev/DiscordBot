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

// https://discord.com/developers/docs/resources/channel#embed-object-embed-field-structure
class Field implements \Serializable{

	/** @var string 256 characters */
	private $name;

	/** @var string 2048 characters */
	private $value;

	/** @var bool */
	private $inline = false;

	public function __construct(string $name, string $value, bool $inline = false){
		$this->setName($name);
		$this->setValue($value);
		$this->setInline($inline);
	}

	public function getName(): string{
		return $this->name;
	}

	public function setName(string $name): void{
		if(strlen($name) > 256){
			throw new \AssertionError("Embed field name can only have up to 256 characters.");
		}
		$this->name = $name;
	}

	public function getValue(): string{
		return $this->value;
	}

	public function setValue(string $value): void{
		if(strlen($value) > 2048){
			throw new \AssertionError("Embed field value can only have up to 2048 characters.");
		}
		$this->value = $value;
	}

	public function isInline(): bool{
		return $this->inline;
	}

	public function setInline(bool $inline): void{
		$this->inline = $inline;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->name,
			$this->value,
			$this->inline
		]);
	}

	public function unserialize($data): void{
		[
			$this->name,
			$this->value,
			$this->inline
		] = unserialize($data);
	}
}