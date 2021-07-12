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

namespace JaxkDev\DiscordBot\Bot\Handlers;

use Discord\Discord;
use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Guild\Ban as DiscordBan;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Invite as DiscordInvite;
use Discord\Parts\Guild\Role as DiscordRole;
use Discord\Parts\Permissions\RolePermission;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Bot\ModelConverter;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DataDump;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventBanAdd;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventBanRemove;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventChannelCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventChannelDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventChannelUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventInviteCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventInviteDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMemberJoin;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMemberLeave;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMemberUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMessageDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMessageSent;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMessageUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventRoleCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventRoleDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventRoleUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventServerJoin;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventServerLeave;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventServerUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventReady;
use JaxkDev\DiscordBot\Communication\Protocol;
use pocketmine\utils\MainLogger;

class DiscordEventHandler{

	/** @var Client */
	private $client;

	public function __construct(Client $client){
		$this->client = $client;
	}

	public function registerEvents(): void{
		$discord = $this->client->getDiscordClient();
		$discord->on("MESSAGE_CREATE", [$this, "onMessageCreate"]);
		$discord->on("MESSAGE_DELETE", [$this, "onMessageDelete"]);
		$discord->on("MESSAGE_UPDATE", [$this, "onMessageUpdate"]);  //AKA Edit

		$discord->on("GUILD_MEMBER_ADD", [$this, "onMemberJoin"]);
		$discord->on("GUILD_MEMBER_REMOVE", [$this, "onMemberLeave"]);
		$discord->on("GUILD_MEMBER_UPDATE", [$this, "onMemberUpdate"]);   //Includes Roles,nickname etc

		$discord->on("GUILD_CREATE", [$this, "onGuildJoin"]);
		$discord->on("GUILD_UPDATE", [$this, "onGuildUpdate"]);
		$discord->on("GUILD_DELETE", [$this, "onGuildLeave"]);

		$discord->on("CHANNEL_CREATE", [$this, "onChannelCreate"]);
		$discord->on("CHANNEL_UPDATE", [$this, "onChannelUpdate"]);
		$discord->on("CHANNEL_DELETE", [$this, "onChannelDelete"]);

		$discord->on("GUILD_ROLE_CREATE", [$this, "onRoleCreate"]);
		$discord->on("GUILD_ROLE_UPDATE", [$this, "onRoleUpdate"]);
		$discord->on("GUILD_ROLE_DELETE", [$this, "onRoleDelete"]);

		$discord->on("INVITE_CREATE", [$this, "onInviteCreate"]);
		$discord->on("INVITE_DELETE", [$this, "onInviteDelete"]);

		$discord->on("GUILD_BAN_ADD", [$this, "onBanAdd"]);
		$discord->on("GUILD_BAN_REMOVE", [$this, "onBanRemove"]);

		/*$discord->on("PRESENCE_UPDATE", function(...$args){
			var_dump($args);
		});*/

		/*$discord->on("VOICE_STATE_UPDATE", function (DiscordVoiceStateUpdate $state, Discord $discord) {
			var_dump($state);
		});*/

		/*
		 * TODO:
		 * - Voice State Update (track members voice activity, join voice channel, leave voice channel, self deafen/mute)
		 * - Reactions (Probably wont store previous reactions, could be very large, add fetchReactions into API.)
		 *
		 * TODO (TBD):
		 * - Presence updates.
		 * - Pins (Note event only emits the pins for the channel not if one was added/deleted/unpinned etc, handle plugin side to check against storage.)
		 */
	}

	/*
Some timing notes.
array(5) {
  ["server"]=>
  array(2) {
    [0]=>
    float(0.00259)
    [1]=>
    int(46)
  }
  ["channel"]=>
  array(2) {
    [0]=>
    float(0.33944)
    [1]=>
    int(2721)
  }
  ["role"]=>
  array(2) {
    [0]=>
    float(0.03372)
    [1]=>
    int(1497)
  }
  ["member"]=>
  array(2) {
    [0]=>
    float(0.11359)
    [1]=>
    int(436)
  }
  ["user"]=>
  array(2) {
    [0]=>
    float(0.00466)
    [1]=>
    int(259)
  }
}

array(5) {
  ["server"]=>
  array(2) {
    [0]=>
    float(0.012749999999999975)
    [1]=>
    int(46)
  }
  ["channel"]=>
  array(2) {
    [0]=>
    float(0.30067999999999995)
    [1]=>
    int(2721)
  }
  ["role"]=>
  array(2) {
    [0]=>
    float(0.03301)
    [1]=>
    int(1497)
  }
  ["member"]=>
  array(2) {
    [0]=>
    float(0.17436000000000001)
    [1]=>
    int(435)
  }
  ["user"]=>
  array(2) {
    [0]=>
    float(0.01252)
    [1]=>
    int(258)
  }
}
	 */

	public function onReady(): void{
		if($this->client->getThread()->getStatus() !== Protocol::THREAD_STATUS_STARTED){
			MainLogger::getLogger()->warning("Closing thread, unexpected state change.");
			$this->client->close();
		}

		// Register all other events.
		$this->registerEvents();

		// Dump all discord data.
		$pk = new DataDump();
		$pk->setTimestamp(time());

		MainLogger::getLogger()->debug("Starting the data pack, please be patient.");
		$t = microtime(true);
		$mem = memory_get_usage(true);

		$client = $this->client->getDiscordClient();

		/** @var DiscordGuild $guild */
		foreach($client->guilds as $guild){
			$pk->addServer(ModelConverter::genModelServer($guild));

			/** @var RolePermission $permissions */
			$permissions = $guild->members->offsetGet($client->id)->getPermissions();

			if($permissions->ban_members){
				/** @noinspection PhpUnhandledExceptionInspection */
				$guild->bans->freshen()->done(function() use ($guild){
					MainLogger::getLogger()->debug("Successfully fetched ".sizeof($guild->bans)." bans from server '".
						$guild->name."' (".$guild->id.")");
					if(sizeof($guild->bans) === 0) return;
					$pk = new DataDump();
					$pk->setTimestamp(time());
					/** @var DiscordBan $ban */
					foreach($guild->bans as $ban){
						$pk->addBan(ModelConverter::genModelBan($ban));
					}
					$this->client->getThread()->writeOutboundData($pk);
				}, function() use ($guild){
					MainLogger::getLogger()->warning("Failed to fetch bans from server '".$guild->name."' (".$guild->id.")");
				});
			}else{
				MainLogger::getLogger()->notice("Cannot fetch bans from server '".$guild->name."' (".$guild->id.
					"), Bot does not have 'ban_members' permission.");
			}

			/** @var DiscordChannel $channel */
			foreach($guild->channels as $channel){
				//Webhooks need freshen.
				$c = ModelConverter::genModelChannel($channel);
				if($c !== null) $pk->addChannel($c);
			}

			/** @var DiscordRole $role */
			foreach($guild->roles as $role){
				$pk->addRole(ModelConverter::genModelRole($role));
			}

			if($permissions->manage_guild){
				/** @noinspection PhpUnhandledExceptionInspection */
				$guild->invites->freshen()->done(function() use ($guild){
					MainLogger::getLogger()->debug("Successfully fetched ".sizeof($guild->invites).
						" invites from server '".$guild->name."' (".$guild->id.")");
					if(sizeof($guild->invites) === 0) return;
					$pk = new DataDump();
					$pk->setTimestamp(time());
					/** @var DiscordInvite $invite */
					foreach($guild->invites as $invite){
						$pk->addInvite(ModelConverter::genModelInvite($invite));
					}
					$this->client->getThread()->writeOutboundData($pk);
				}, function() use ($guild){
					MainLogger::getLogger()->warning("Failed to fetch invites from server '".$guild->name."' (".$guild->id.")");
				});
			}else{
				MainLogger::getLogger()->notice("Cannot fetch invites from server '".$guild->name."' (".$guild->id.
					"), Bot does not have 'manage_guild' permission.");
			}

			/** @var DiscordMember $member */
			foreach($guild->members as $member){
				$pk->addMember(ModelConverter::genModelMember($member));
			}
		}

		/** @var DiscordUser $user */
		foreach($client->users as $user){
			$pk->addUser(ModelConverter::genModelUser($user));
		}

		$pk->setBotUser(ModelConverter::genModelUser($client->user));

		MainLogger::getLogger()->debug("Data pack took: ".round(microtime(true)-$t, 5)."s & ".
			round(((memory_get_usage(true)-$mem)/1024)/1024, 4)."mb of memory, Final size: ".$pk->getSize());

		//Very important to check status before overwriting, can cause dangerous behaviour.
		if($this->client->getThread()->getStatus() !== Protocol::THREAD_STATUS_STARTED){
			MainLogger::getLogger()->warning("Closing thread, unexpected state change.");
			$this->client->close();
		}

		$this->client->getThread()->writeOutboundData($pk);

		$this->client->getThread()->setStatus(Protocol::THREAD_STATUS_READY);
		MainLogger::getLogger()->info("Client '".$client->username."#".$client->discriminator."' ready.");

		$this->client->getThread()->writeOutboundData(new EventReady());
		$this->client->getCommunicationHandler()->sendHeartbeat();
	}

	public function onMessageCreate(DiscordMessage $message, Discord $discord): void{
		if(!$this->checkMessage($message)) return;
		//if($message->author->id === "305060807887159296") $message->react("❤️");
		//Dont ask questions...
		$packet = new EventMessageSent(ModelConverter::genModelMessage($message));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onMessageUpdate(DiscordMessage $message, Discord $discord): void{
		if(!$this->checkMessage($message)) return;
		$packet = new EventMessageUpdate(ModelConverter::genModelMessage($message));
		$this->client->getThread()->writeOutboundData($packet);
	}

	/**
	 * TODO, Linked to DiscordMessageDeleted.php todo.
	 * @param DiscordMessage|\stdClass $message
	 * @param Discord                  $discord
	 */
	public function onMessageDelete($message, Discord $discord): void{
		$packet = new EventMessageDelete($message->id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onMemberJoin(DiscordMember $member, Discord $discord): void{
		$packet = new EventMemberJoin(ModelConverter::genModelMember($member), ModelConverter::genModelUser($member->user));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onMemberUpdate(DiscordMember $member, Discord $discord): void{
		$packet = new EventMemberUpdate(ModelConverter::genModelMember($member));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onMemberLeave(DiscordMember $member, Discord $discord): void{
		$packet = new EventMemberLeave($member->guild_id.".".$member->id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onGuildJoin(DiscordGuild $guild, Discord $discord): void{
		$channels = [];
		/** @var DiscordChannel $channel */
		foreach($guild->channels as $channel){
			$c = ModelConverter::genModelChannel($channel);
			if($c !== null) $channels[] = $c;
		}
		$roles = [];
		/** @var DiscordRole $role */
		foreach($guild->roles as $role){
			$roles[] = ModelConverter::genModelRole($role);
		}
		$members = [];
		/** @var DiscordMember $member */
		foreach($guild->members as $member){
			$members[] = ModelConverter::genModelMember($member);
		}

		$packet = new EventServerJoin(ModelConverter::genModelServer($guild), $channels, $members, $roles);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onGuildUpdate(DiscordGuild $guild, Discord $discord): void{
		$packet = new EventServerUpdate(ModelConverter::genModelServer($guild));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onGuildLeave(DiscordGuild $guild, Discord $discord): void{
		$packet = new EventServerLeave($guild->id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onChannelCreate(DiscordChannel $channel, Discord $discord): void{
		$c = ModelConverter::genModelChannel($channel);
		if($c === null) return;
		$packet = new EventChannelCreate($c);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onChannelUpdate(DiscordChannel $channel, Discord $discord): void{
		$c = ModelConverter::genModelChannel($channel);
		if($c === null) return;
		$packet = new EventChannelUpdate($c);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onChannelDelete(DiscordChannel $channel, Discord $discord): void{
		$packet = new EventChannelDelete($channel->id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onRoleCreate(DiscordRole $role, Discord $discord): void{
		$packet = new EventRoleCreate(ModelConverter::genModelRole($role));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onRoleUpdate(DiscordRole $role, Discord $discord): void{
		$packet = new EventRoleUpdate(ModelConverter::genModelRole($role));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onRoleDelete(DiscordRole $role, Discord $discord): void{
		$packet = new EventRoleDelete($role->id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onInviteCreate(DiscordInvite $invite, Discord $discord): void{
		$packet = new EventInviteCreate(ModelConverter::genModelInvite($invite));
		$this->client->getThread()->writeOutboundData($packet);
	}

	/**
	 * @param \stdClass $invite {channel_id: str, guild_id: str, code: str}
	 * @param Discord   $discord
	 */
	public function onInviteDelete(\stdClass $invite, Discord $discord): void{
		$packet = new EventInviteDelete($invite->code);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onBanAdd(DiscordBan $ban, Discord $discord): void{
		//No reason unless you freshen bans which is only possible with ban_members permission.
		$g = $ban->guild;
		/** @var DiscordMember|null $m */
		$m = $g->members->offsetGet($discord->user->id);
		if($m !== null and $m->getPermissions()->ban_members){
			//Get ban reason.
			/** @noinspection PhpUnhandledExceptionInspection */ //Impossible.
			$g->bans->freshen()->done(function() use ($ban, $g){
				//Got latest bans so we can fetch reason unless it was unbanned in like 0.01s
				/** @var DiscordBan|null $b */
				$b = $g->bans->offsetGet($ban->user_id);
				if($b !== null){
					MainLogger::getLogger()->debug("Successfully fetched bans, attached reason to new ban event.");
					$packet = new EventBanAdd(ModelConverter::genModelBan($b));
					$this->client->getThread()->writeOutboundData($packet);
				}else{
					MainLogger::getLogger()->debug("No ban after freshen ??? (IMPORTANT LOGIC ERROR)");
					$packet = new EventBanAdd(ModelConverter::genModelBan($ban));
					$this->client->getThread()->writeOutboundData($packet);
				}
			}, function() use ($ban){
				//Failed so just send ban with no reason.
				MainLogger::getLogger()->debug("Failed to fetch bans even with ban_members permission, using old ban object.");
				$packet = new EventBanAdd(ModelConverter::genModelBan($ban));
				$this->client->getThread()->writeOutboundData($packet);
			});
		}else{
			MainLogger::getLogger()->debug("Bot does not have ban_members permission so no reason could be attached to this ban.");
			$packet = new EventBanAdd(ModelConverter::genModelBan($ban));
			$this->client->getThread()->writeOutboundData($packet);
		}
	}

	public function onBanRemove(DiscordBan $ban, Discord $discord): void{
		$packet = new EventBanRemove($ban->guild_id.".".$ban->user_id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	/**
	 * Checks if we handle this type of message in this type of channel.
	 * @param DiscordMessage $message
	 * @return bool
	 */
	private function checkMessage(DiscordMessage $message): bool{
		// Can be user if bot doesnt have correct intents enabled on discord developer dashboard.
		if($message->author === null) return false; //"Shouldn't" happen now...
		if($message->author->id === $this->client->getDiscordClient()->id) return false;

		// Other types of messages not used right now.
		if($message->type !== DiscordMessage::TYPE_NORMAL and $message->type !== DiscordMessage::TYPE_REPLY) return false;
		if(($message->content??"") === "" and $message->embeds->count() === 0) return false;
		// ^ Images/Files/Spotify/Games etc

		return true;
	}
}