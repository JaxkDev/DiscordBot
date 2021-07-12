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

	public static function getDiscordSnowflakeTimestamp(string $snowflake): int{
		return intval(floor(((intval($snowflake) >> 22) + 1420070400000) / 1000));
	}

	/** Checks a discord snowflake by verifying the timestamp at when it was created. */
	public static function validDiscordSnowflake(string $snowflake): bool{
		$len = strlen($snowflake);
		if($len < 17 or $len > 18) return false;
		$timestamp = self::getDiscordSnowflakeTimestamp($snowflake);
		if($timestamp > time()+86400 or $timestamp <= 1420070400) return false; //+86400 (24h for any timezone problems)
		return true;
	}
}