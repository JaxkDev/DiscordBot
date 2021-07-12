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

namespace JaxkDev\DiscordBot\Communication;

abstract class Protocol{

	const
		PACKETS_PER_TICK 	= 50, 	// How many packets should be processed per tick ?
		HEARTBEAT_ALLOWANCE = 10;	// How long between last known heartbeat until declared dead. (seconds)

	const
		THREAD_STATUS_STARTING 	= 0,
		THREAD_STATUS_STARTED 	= 1,
		THREAD_STATUS_READY 	= 2,
		THREAD_STATUS_CLOSING 	= 8,
		THREAD_STATUS_CLOSED 	= 9;
}