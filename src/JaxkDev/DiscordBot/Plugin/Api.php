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

use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestAddReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestBroadcastTyping;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestEditMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestAddRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestKickMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestLeaveServer;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestPinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveAllReactions;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUnpinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateActivity;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateNickname;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateRole;
use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;
use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Models\Messages\Webhook;
use JaxkDev\DiscordBot\Models\Role;
use function JaxkDev\DiscordBot\Libs\React\Promise\reject as rejectPromise;

/*
 * TODO:
 * - Fetch Message
 * - Fetch Channel Pins (entire message obj's)
 *
 * v2.1+ :
 * - Register listener (messages, reactions etc)
 * - Unregister listener
 *
 * Pending:
 * - Create Role (Cannot create position, https://github.com/discord-php/DiscordPHP/issues/556)
 * - Update Role (Cannot update position, ^)
 * - Update Channel (Majority of attributes cant be updated, https://github.com/discord-php/DiscordPHP/issues/555)
 *
 * Tested:
 * - Unpin Message
 * - Pin Message
 * - Create Channel
 * - Leave Server
 * - Ban
 * - Unban
 * - Kick
 * - Delete Role
 * - Remove Role
 * - Add Role
 * - Remove Reactions(bulk/user)
 * - Remove Reaction(individual)
 * - Add Reaction
 * - Send Message(+Embed/+Reply)
 * - Edit Message(+Embed/+Reply)
 * - Delete Channel
 * - Delete Message
 * - Delete Invite
 * - Update Nickname
 * - Broadcast Typing
 */

/**
 * For internal and developers use for interacting with the discord bot.
 *
 * Model creation methods are static, note you can initialise your own models but
 * these functions ensure the required fields are present & valid for creation and use..
 *
 *
 * @see Main::getApi() To get instance.
 * @see Storage For all discord data.
 */
class Api{

	/** @var Main */
	private $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	//Technically we could make our own servers but that would mean bot with owner permissions and that could get dodgy.

	/**
	 * Leave a discord server.
	 *
	 * @param string $server_id
	 * @return PromiseInterface
	 */
	public function leaveServer(string $server_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($server_id)){
			return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
		}
		$pk = new RequestLeaveServer($server_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Pin a message to the channel.
	 *
	 * @param string $channel_id
	 * @param string $message_id
	 * @return PromiseInterface
	 */
	public function pinMessage(string $channel_id, string $message_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		if(!Utils::validDiscordSnowflake($message_id)){
			return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
		}
		$pk = new RequestPinMessage($channel_id, $message_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Un-pin a message to the channel.
	 *
	 * @param string $channel_id
	 * @param string $message_id
	 * @return PromiseInterface
	 */
	public function unpinMessage(string $channel_id, string $message_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		if(!Utils::validDiscordSnowflake($message_id)){
			return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
		}
		$pk = new RequestUnpinMessage($channel_id, $message_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Create a role.
	 *
	 * @param Role $role
	 * @return PromiseInterface
	 */
	public function createRole(Role $role): PromiseInterface{
		$pk = new RequestCreateRole($role);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Update a already created role, ID must be present.
	 *
	 * @param Role $role
	 * @return PromiseInterface
	 */
	public function updateRole(Role $role): PromiseInterface{
		if($role->getId() === null){
			return rejectPromise(new ApiRejection("Role ID must be present when updating."));
		}
		$pk = new RequestUpdateRole($role);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Delete a role.
	 *
	 * @param string $server_id
	 * @param string $role_id
	 * @return PromiseInterface
	 */
	public function deleteRole(string $server_id, string $role_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($server_id)){
			return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
		}
		if(!Utils::validDiscordSnowflake($role_id)){
			return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
		}
		$pk = new RequestDeleteRole($server_id, $role_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Remove a role from a member.
	 *
	 * @param string $member_id
	 * @param string $role_id
	 * @return PromiseInterface
	 */
	public function removeRole(string $member_id, string $role_id): PromiseInterface{
		[$sid, $uid] = explode(".", $member_id);
		if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
			return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
		}
		if(!Utils::validDiscordSnowflake($role_id)){
			return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
		}
		$pk = new RequestRemoveRole($sid, $uid, $role_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Give the member a role.
	 *
	 * @param string $member_id
	 * @param string $role_id
	 * @return PromiseInterface
	 */
	public function addRole(string $member_id, string $role_id): PromiseInterface{
		[$sid, $uid] = explode(".", $member_id);
		if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
			return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
		}
		if(!Utils::validDiscordSnowflake($role_id)){
			return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
		}
		$pk = new RequestAddRole($sid, $uid, $role_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Remove a single reaction.
	 *
	 * @param string $channel_id
	 * @param string $message_id
	 * @param string $user_id
	 * @param string $emoji Raw emoji eg '👍'
	 * @return PromiseInterface
	 */
	public function removeReaction(string $channel_id, string $message_id, string $user_id, string $emoji): PromiseInterface{
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		if(!Utils::validDiscordSnowflake($message_id)){
			return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
		}
		if(!Utils::validDiscordSnowflake($user_id)){
			return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
		}
		$pk = new RequestRemoveReaction($channel_id, $message_id, $user_id, $emoji);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Remove all reactions on a message.
	 *
	 * TODO HIGH PRIORITY, Investigate when emoji present it fails (Bad request) on PHP7 but works fine with PHP8
	 *
	 * @param string      $channel_id
	 * @param string      $message_id
	 * @param string|null $emoji If no emoji specified ALL reactions by EVERYONE will be deleted,
	 *                           if specified everyone's reaction with that emoji will be removed.
	 * @return PromiseInterface
	 */
	public function removeAllReactions(string $channel_id, string $message_id, ?string $emoji = null): PromiseInterface{
		if($emoji !== null and PHP_VERSION_ID < 80000){
			return rejectPromise(new ApiRejection("removeAllReactions with emoji does not currently work on PHP7 :("));
		}
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		if(!Utils::validDiscordSnowflake($message_id)){
			return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
		}
		$pk = new RequestRemoveAllReactions($channel_id, $message_id, $emoji);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Add a reaction to a message.
	 *
	 * Note, If you have already reacted with the emoji provided it will still respond with a successful promise resolution.
	 *
	 * @param string $channel_id
	 * @param string $message_id
	 * @param string $emoji			MUST BE THE ACTUAL EMOJI CHARACTER, (Custom/Private emoji's not yet supported) eg '👍'
	 * @return PromiseInterface
	 */
	public function addReaction(string $channel_id, string $message_id, string $emoji): PromiseInterface{
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		if(!Utils::validDiscordSnowflake($message_id)){
			return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
		}
		$pk = new RequestAddReaction($channel_id, $message_id, $emoji);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * "Generally bots should not implement this. However, if a bot is responding to a command and expects the computation
	 * to take a few seconds, this endpoint may be called to let the user know that the bot is processing their message."
	 * The 'typing' effect will last for 10s
	 *
	 * DO NOT ABUSE THIS.
	 *
	 * @param string $channel_id
	 * @return PromiseInterface
	 */
	public function broadcastTyping(string $channel_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		$pk = new RequestBroadcastTyping($channel_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Sends the new activity to replace the current one the bot has.
	 *
	 * @param Activity $activity
	 * @return PromiseInterface
	 */
	public function updateActivity(Activity $activity): PromiseInterface{
		$pk = new RequestUpdateActivity($activity);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Attempt to ban a member.
	 *
	 * @param Ban $ban
	 * @return PromiseInterface
	 */
	public function initialiseBan(Ban $ban): PromiseInterface{
		$pk = new RequestInitialiseBan($ban);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Attempt to revoke a ban.
	 *
	 * @param string $server_id
	 * @param string $user_id
	 * @return PromiseInterface
	 */
	public function revokeBan(string $server_id, string $user_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($server_id)){
			return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
		}
		if(!Utils::validDiscordSnowflake($user_id)){
			return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
		}
		$pk = new RequestRevokeBan($server_id, $user_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Attempt to kick a member.
	 *
	 * @param string $member_id
	 * @return PromiseInterface
	 */
	public function kickMember(string $member_id): PromiseInterface{
		[$sid, $uid] = explode(".", $member_id);
		if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
			return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
		}
		$pk = new RequestKickMember($sid, $uid);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Sends the Message to discord.
	 *
	 * @param Message $message
	 * @return PromiseInterface
	 */
	public function sendMessage(Message $message): PromiseInterface{
		if($message instanceof Webhook){
			return rejectPromise(new ApiRejection("Webhook messages cannot be sent, only received."));
		}
		$pk = new RequestSendMessage($message);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Edit a sent message.
	 *
	 * Note you can't convert a 'REPLY' message to a normal 'MESSAGE'.
	 *
	 * @param Message $message
	 * @return PromiseInterface
	 * @see Api::sendMessage For sending a message.
	 * @see Api::deleteMessage For deleting a sent message.
	 */
	public function editMessage(Message $message): PromiseInterface{
		if($message->getId() === null){
			return rejectPromise(new ApiRejection("Message must have a valid ID to be able to edit it."));
		}
		$pk = new RequestEditMessage($message);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Delete a sent message.
	 *
	 * @param string $message_id
	 * @param string $channel_id
	 * @return PromiseInterface
	 */
	public function deleteMessage(string $message_id, string $channel_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		if(!Utils::validDiscordSnowflake($message_id)){
			return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
		}
		$pk = new RequestDeleteMessage($message_id, $channel_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Create a server channel.
	 *
	 * @param ServerChannel $channel CategoryChannel, TextChannel or VoiceChannel.
	 * @return PromiseInterface
	 */
	public function createChannel(ServerChannel $channel): PromiseInterface{
		$pk = new RequestCreateChannel($channel);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Update a server channel, ID Must be present.
	 * TODO Keep checking Discord.PHP seems they missed a few updatable attributes...
	 *
	 * @param ServerChannel $channel
	 * @return PromiseInterface
	 */
	public function updateChannel(ServerChannel $channel): PromiseInterface{
		$pk = new RequestUpdateChannel($channel);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Delete a channel in a server, you cannot delete private channels (DM's)
	 *
	 * @param string $server_id
	 * @param string $channel_id
	 * @return PromiseInterface
	 */
	public function deleteChannel(string $server_id, string $channel_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($server_id)){
			return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
		}
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		$pk = new RequestDeleteChannel($server_id, $channel_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Initialise if possible the given invite.
	 *
	 * @param Invite $invite
	 * @return PromiseInterface
	 * @see Api::revokeInvite() For revoking an initialised invite.
	 */
	public function initialiseInvite(Invite $invite): PromiseInterface{
		$pk = new RequestInitialiseInvite($invite);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Revoke an initialised invite.
	 *
	 * @param string $server_id
	 * @param string $invite_code
	 * @return PromiseInterface
	 */
	public function revokeInvite(string $server_id, string $invite_code): PromiseInterface{
		if(!Utils::validDiscordSnowflake($server_id)){
			return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
		}
		$pk = new RequestRevokeInvite($server_id, $invite_code);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Update a members nickname (set to null to remove)
	 *
	 * @param string $member_id
	 * @param null|string $nickname Null to remove nickname.
	 * @return PromiseInterface
	 */
	public function updateNickname(string $member_id, ?string $nickname = null): PromiseInterface{
		[$sid, $uid] = explode(".", $member_id);
		if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
			return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
		}
		$pk = new RequestUpdateNickname($sid, $uid, $nickname);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}
}