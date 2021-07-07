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

abstract class Utils{

	/** Checks a discord snowflake by verifying the timestamp at when it was created. */
	public static function validDiscordSnowflake(string $snowflake): bool{
		$len = strlen($snowflake);
		if($len < 17 or $len > 18) return false;
		$timestamp = floor(((intval($snowflake) >> 22) + 1420070400000) / 1000);
		if($timestamp > time() or $timestamp <= 1420070400) return false;
		return true;
	}
}