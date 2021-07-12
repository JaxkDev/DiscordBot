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

namespace JaxkDev\DiscordBot\Plugin\Events;

/**
 * DiscordBot has connected and we are now in contact with discord.
 * @see DiscordClosed Emitted when DiscordBot disconnects.
 */
class DiscordReady extends DiscordBotEvent{}