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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestUpdateActivity extends Packet{

	/** @var Activity */
	private $activity;

	public function __construct(Activity $activity){
		parent::__construct();
		$this->activity = $activity;
	}

	public function getActivity(): Activity{
		return $this->activity;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->activity]);
	}

	public function unserialize($data): void{
		[$this->UID, $this->activity] = unserialize($data);
	}
}