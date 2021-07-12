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

// Remember categories are classed as channels.
class ChannelPermissions extends Permissions{

	/**
	 * @return Array<string, int>
	 */
	static function getPossiblePermissions(): array{
		return array_merge(Permissions::ALL_PERMISSIONS, Permissions::TEXT_PERMISSIONS, Permissions::VOICE_PERMISSIONS);
	}
}