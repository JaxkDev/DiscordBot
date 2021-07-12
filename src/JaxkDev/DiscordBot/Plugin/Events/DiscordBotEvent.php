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

use pocketmine\event\plugin\PluginEvent;

/**
 * All events are cancellable [Excluding Ready/Closed event].
 *
 * Cancelling events will result in NO DEFAULT messages being sent however please note that the change to Storage will
 * not be reflected until after the event so in event `DiscordMemberDeleted` for example the storage will still have the full member/user model.
 * But once you and all other plugins have finished handling the event the member will be deleted from storage
 *
 * Also note cancelling the event DOES NOT 'undo the cause', eg `DiscordServerJoined` event if cancelled
 * the bot will still remain in that server, if you want to 'undo the cause' you must do so yourself
 * eg on the `DiscordServerJoined` event you can use the API to leave the server joined.
 */
class DiscordBotEvent extends PluginEvent{}