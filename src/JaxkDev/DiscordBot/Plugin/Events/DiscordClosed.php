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
 * DiscordBot has disconnected and we are no longer in contact with discord.
 * @see EventReady Emitted when DiscordBot connects.
 */
class DiscordClosed extends DiscordBotEvent{}