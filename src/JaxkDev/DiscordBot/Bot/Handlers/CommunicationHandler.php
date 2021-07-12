<?php /** @noinspection PhpUnhandledExceptionInspection */

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

use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Embed\Embed as DiscordEmbed;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Invite as DiscordInvite;
use Discord\Parts\Guild\Role as DiscordRole;
use Discord\Parts\User\Activity as DiscordActivity;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use Discord\Repository\Guild\InviteRepository as DiscordInviteRepository;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Bot\ModelConverter;
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
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUnpinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateNickname;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateRole;
use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Communication\Packets\Resolution;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateActivity;
use JaxkDev\DiscordBot\Communication\Protocol;
use JaxkDev\DiscordBot\Models\Channels\CategoryChannel;
use JaxkDev\DiscordBot\Models\Channels\TextChannel;
use JaxkDev\DiscordBot\Models\Channels\VoiceChannel;
use JaxkDev\DiscordBot\Models\Messages\Reply;
use pocketmine\utils\MainLogger;

class CommunicationHandler{

	/** @var Client */
	private $client;

	/** @var float|null */
	private $lastHeartbeat = null;

	public function __construct(Client $client){
		$this->client = $client;
	}

	//--- Handlers:

	public function handle(Packet $pk): void{
		//Internals:
		if($pk instanceof Heartbeat){
			$this->lastHeartbeat = $pk->getHeartbeat();
			return;
		}

		//API Check:
		if($this->client->getThread()->getStatus() !== Protocol::THREAD_STATUS_READY){
			$this->resolveRequest($pk->getUID(), false, "Thread not ready for API Requests.");
			return;
		}

		//API Packets:
		if($pk instanceof RequestUpdateNickname) $this->handleUpdateNickname($pk);
		elseif($pk instanceof RequestUpdateActivity) $this->handleUpdateActivity($pk);
		elseif($pk instanceof RequestBroadcastTyping) $this->handleBroadcastTyping($pk);
		elseif($pk instanceof RequestSendMessage) $this->handleSendMessage($pk);
		elseif($pk instanceof RequestEditMessage) $this->handleEditMessage($pk);
		elseif($pk instanceof RequestAddReaction) $this->handleAddReaction($pk);
		elseif($pk instanceof RequestRemoveReaction) $this->handleRemoveReaction($pk);
		elseif($pk instanceof RequestRemoveAllReactions) $this->handleRemoveAllReactions($pk);
		elseif($pk instanceof RequestDeleteMessage) $this->handleDeleteMessage($pk);
		elseif($pk instanceof RequestPinMessage) $this->handlePinMessage($pk);
		elseif($pk instanceof RequestUnpinMessage) $this->handleUnpinMessage($pk);
		elseif($pk instanceof RequestAddRole) $this->handleAddRole($pk);
		elseif($pk instanceof RequestRemoveRole) $this->handleRemoveRole($pk);
		elseif($pk instanceof RequestCreateRole) $this->handleCreateRole($pk);
		elseif($pk instanceof RequestUpdateRole) $this->handleUpdateRole($pk);
		elseif($pk instanceof RequestDeleteRole) $this->handleDeleteRole($pk);
		elseif($pk instanceof RequestKickMember) $this->handleKickMember($pk);
		elseif($pk instanceof RequestInitialiseInvite) $this->handleInitialiseInvite($pk);
		elseif($pk instanceof RequestRevokeInvite) $this->handleRevokeInvite($pk);
		elseif($pk instanceof RequestCreateChannel) $this->handleCreateChannel($pk);
		elseif($pk instanceof RequestUpdateChannel) $this->handleUpdateChannel($pk);
		elseif($pk instanceof RequestDeleteChannel) $this->handleDeleteChannel($pk);
		elseif($pk instanceof RequestInitialiseBan) $this->handleInitialiseBan($pk);
		elseif($pk instanceof RequestRevokeBan) $this->handleRevokeBan($pk);
		elseif($pk instanceof RequestLeaveServer) $this->handleLeaveServer($pk);
	}

	private function handleUnpinMessage(RequestUnpinMessage $pk): void{
		$this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
			$this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $message) use($channel, $pk){
				$channel->unpinMessage($message)->then(function() use($pk){
					$this->resolveRequest($pk->getUID(), true, "Successfully unpinned the message.");
				}, function(\Throwable $e) use($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to unpin the message.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to pin the message ({$pk->getUID()}) - {$e->getMessage()}");
				});
			});
		});
	}

	private function handlePinMessage(RequestPinMessage $pk): void{
		$this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
			$this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $message) use($channel, $pk){
				$channel->pinMessage($message)->then(function() use($pk){
					$this->resolveRequest($pk->getUID(), true, "Successfully pinned the message.");
				}, function(\Throwable $e) use($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to pin the message.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to pin the message ({$pk->getUID()}) - {$e->getMessage()}");
				});
			});
		});
	}

	private function handleLeaveServer(RequestLeaveServer $pk): void{
		$this->getServer($pk, $pk->getServerId(), function(DiscordGuild $guild) use($pk){
			$this->client->getDiscordClient()->guilds->leave($guild)->then(function() use($pk){
				$this->resolveRequest($pk->getUID());
			}, function(\Throwable $e) use($pk){
				//Shouldn't happen unless not in server/connection issues.
				$this->resolveRequest($pk->getUID(), false, "Failed to leave server.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to leave server? ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleCreateRole(RequestCreateRole $pk): void{
		$this->getServer($pk, $pk->getRole()->getServerId(), function(DiscordGuild $guild) use($pk){
			$r = $pk->getRole();
			$guild->createRole([
				'name' => $r->getName(),
				'color' => $r->getColour(),
				'permissions' => $r->getPermissions()->getBitwise(),
				'hoist' => $r->isHoisted(),
				'position' => $r->getHoistedPosition(),
				'mentionable' => $r->isMentionable()
			])->then(function(DiscordRole $role) use($pk){
				$this->resolveRequest($pk->getUID(), true, "Created role.", [ModelConverter::genModelRole($role)]);
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to create role.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to create role ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleUpdateRole(RequestUpdateRole $pk): void{
		if($pk->getRole()->getId() === null){
			$this->resolveRequest($pk->getUID(), false, "Failed to update role.", ["Role ID must be present."]);
			return;
		}
		$this->getServer($pk, $pk->getRole()->getServerId(), function(DiscordGuild $guild) use($pk){
			$guild->roles->fetch($pk->getRole()->getId())->then(function(DiscordRole $role) use($guild, $pk){
				$role->position = $pk->getRole()->getHoistedPosition();
				$role->hoist = $pk->getRole()->isHoisted();
				$role->mentionable = $pk->getRole()->isMentionable();
				$role->name = $pk->getRole()->getName();
				$role->color = $pk->getRole()->getColour();
				$role->permissions->bitwise = $pk->getRole()->getPermissions()->getBitwise();
				$guild->roles->save($role)->then(function(DiscordRole $role) use($pk){
					$this->resolveRequest($pk->getUID(), true, "Updated role.", [ModelConverter::genModelRole($role)]);
				}, function(\Throwable $e) use($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to update role.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to create role ({$pk->getUID()}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to update role.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to update role ({$pk->getUID()}) - role error: {$e->getMessage()}");
			});
		});
	}

	private function handleDeleteRole(RequestDeleteRole $pk): void{
		$this->getServer($pk, $pk->getServerId(), function(DiscordGuild $guild) use($pk){
			$guild->roles->fetch($pk->getRoleId())->then(function(DiscordRole $role) use($pk, $guild){
				$guild->roles->delete($role)->then(function() use($pk){
					$this->resolveRequest($pk->getUID(), true, "Deleted role.");
				}, function(\Throwable $e) use($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to delete role.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to delete role ({$pk->getUID()}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to fetch role.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to delete role ({$pk->getUID()}) - fetch role: {$e->getMessage()}");
			});
		});
	}

	private function handleRemoveRole(RequestRemoveRole $pk): void{
		$this->getMember($pk, $pk->getServerId(), $pk->getUserId(), function(DiscordMember $dMember) use($pk){
			$dMember->removeRole($pk->getRoleId())->done(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Removed role.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to remove role.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to remove role ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleAddRole(RequestAddRole $pk): void{
		$this->getMember($pk, $pk->getServerId(), $pk->getUserId(), function(DiscordMember $dMember) use($pk){
			$dMember->addRole($pk->getRoleId())->done(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Added role.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to add role.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to add role ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleRemoveReaction(RequestRemoveReaction $pk): void{
		$this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $msg) use($pk){
			$msg->deleteReaction($pk->getUserId() === $this->client->getDiscordClient()->id ? DiscordMessage::REACT_DELETE_ME : DiscordMessage::REACT_DELETE_ID, $pk->getEmoji(), $pk->getUserId())->then(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Successfully removed reaction.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to remove reaction.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to remove reaction ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleRemoveAllReactions(RequestRemoveAllReactions $pk): void{
		$this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $msg) use($pk){
			$msg->deleteReaction(($e = $pk->getEmoji()) === null ? DiscordMessage::REACT_DELETE_ALL : DiscordMessage::REACT_DELETE_EMOJI, $e)->then(function() use($pk, $e){
				$this->resolveRequest($pk->getUID(), true, "Successfully bulk removed all ".($e === null ? "" : "'$e' ")."reactions");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to bulk remove reactions.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to bulk remove reactions ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleAddReaction(RequestAddReaction $pk): void{
		$this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $msg) use($pk){
			$msg->react($pk->getEmoji())->then(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Reaction added.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to react to message.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to react to message ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleCreateChannel(RequestCreateChannel $pk): void{
		$this->getServer($pk, $pk->getChannel()->getServerId(), function(DiscordGuild $guild) use($pk){
			$c = $pk->getChannel();
			/** @var DiscordChannel $dc */
			$dc = $guild->channels->create([
				'name' => $c->getName(),
				'position' => $c->getPosition(),
				'guild_id' => $guild->id
			]);
			if($c->getCategoryId() !== null){
				$dc->parent_id = $c->getCategoryId();
			}
			//todo overwrites. (permissions)
			if($c instanceof CategoryChannel){
				$dc->type = DiscordChannel::TYPE_CATEGORY;
			}elseif($c instanceof VoiceChannel){
				$dc->type = DiscordChannel::TYPE_VOICE;
				$dc->bitrate = $c->getBitrate();
				$dc->user_limit = $c->getMemberLimit();
			}elseif($c instanceof TextChannel){
				$dc->topic = $c->getTopic();
				$dc->nsfw = $c->isNsfw();
				$dc->rate_limit_per_user = $c->getRateLimit()??0;
			}else{
				throw new \AssertionError("What channel type is this ?? '".get_class($c)."'");
			}
			$guild->channels->save($dc)->then(function(DiscordChannel $channel) use($pk){
				$this->resolveRequest($pk->getUID(), true, "Created channel.", [ModelConverter::genModelChannel($channel)]);
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to create channel.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to create channel ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleUpdateChannel(RequestUpdateChannel $pk): void{
		if($pk->getChannel()->getId() === null){
			$this->resolveRequest($pk->getUID(), false, "Failed to update channel.", ["Channel ID must be present."]);
			return;
		}
		$this->getServer($pk, $pk->getChannel()->getServerId(), function(DiscordGuild $guild) use($pk){
			$guild->channels->fetch($pk->getChannel()->getId())->then(function(DiscordChannel $dc) use($guild, $pk){
				$channel = $pk->getChannel();
				$dc->name = $pk->getChannel()->getName();
				$dc->position = $pk->getChannel()->getPosition();
				if($pk->getChannel()->getCategoryId() !== null){
					$dc->parent_id = $pk->getChannel()->getCategoryId();
				}
				//todo overwrites. (permissions)
				if($channel instanceof CategoryChannel){
					if($dc->type !== DiscordChannel::TYPE_CATEGORY){
						$this->resolveRequest($pk->getUID(), false, "Failed to update channel.", ["Channel type change is not allowed."]);
						return;
					}
				}elseif($channel instanceof VoiceChannel){
					if($dc->type !== DiscordChannel::TYPE_VOICE){
						$this->resolveRequest($pk->getUID(), false, "Failed to update channel.", ["Channel type change is not allowed."]);
						return;
					}
					$dc->bitrate = $channel->getBitrate();
					$dc->user_limit = $channel->getMemberLimit();
				}elseif($channel instanceof TextChannel){
					if($dc->type !== DiscordChannel::TYPE_TEXT){
						$this->resolveRequest($pk->getUID(), false, "Failed to update channel.", ["Channel type change is not allowed."]);
						return;
					}
					$dc->topic = $channel->getTopic();
					$dc->nsfw = $channel->isNsfw();
					$dc->rate_limit_per_user = $channel->getRateLimit()??0;
				}else{
					$this->resolveRequest($pk->getUID(), false, "Failed to update channel.", ["Channel type is unknown."]);
					throw new \AssertionError("What channel type is this ?? '".get_class($channel)."'");
				}
				$guild->channels->save($dc)->then(function(DiscordChannel $channel) use($pk){
					$this->resolveRequest($pk->getUID(), true, "Updated channel.", [ModelConverter::genModelChannel($channel)]);
				}, function(\Throwable $e) use($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to update channel.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to update channel ({$pk->getUID()}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to update channel.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to update channel ({$pk->getUID()}) - channel error: {$e->getMessage()}");
			});
		});
	}

	private function handleDeleteChannel(RequestDeleteChannel $pk): void{
		$this->getServer($pk, $pk->getServerId(), function(DiscordGuild $guild) use($pk){
			$this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($guild, $pk){
				$guild->channels->delete($channel)->then(function() use($pk){
					$this->resolveRequest($pk->getUID(), true, "Channel deleted.");
				}, function(\Throwable $e) use($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to delete channel.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to delete channel ({$pk->getUID()}) - {$e->getMessage()}");
				});
			});
		});
	}

	private function handleBroadcastTyping(RequestBroadcastTyping $pk): void{
		$this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
			$channel->broadcastTyping()->done(function() use($pk){
				$this->resolveRequest($pk->getUID());
				MainLogger::getLogger()->debug("BroadcastTyping - success ({$pk->getUID()})");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to broadcast typing.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to broadcast typing ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleUpdateNickname(RequestUpdateNickname $pk): void{
		$this->getMember($pk, $pk->getServerId(), $pk->getUserId(), function(DiscordMember $dMember) use($pk){
			$dMember->setNickname($pk->getNickname())->done(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Updated nickname.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to update nickname.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to update nickname ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleUpdateActivity(RequestUpdateActivity $pk): void{
		$activity = $pk->getActivity();
		$presence = new DiscordActivity($this->client->getDiscordClient(), [
			'name' => $activity->getMessage(),
			'type' => $activity->getType()
		]);

		try{
			$this->client->getDiscordClient()->updatePresence($presence, $activity->getStatus() === Activity::STATUS_IDLE, $activity->getStatus());
			$this->resolveRequest($pk->getUID());
		}catch (\Throwable $e){
			$this->resolveRequest($pk->getUID(), false, $e->getMessage());
		}
	}

	private function handleSendMessage(RequestSendMessage $pk): void{
		$this->getChannel($pk, $pk->getMessage()->getChannelId(), function(DiscordChannel $channel) use($pk){
			$m = $pk->getMessage();
			$e = $m->getEmbed();
			$de = null;
			if($e !== null){
				$de = new DiscordEmbed($this->client->getDiscordClient());
				if($e->getType() !== null) $de->setType($e->getType());
				if($e->getTitle() !== null) $de->setTitle($e->getTitle());
				if($e->getUrl() !== null) $de->setURL($e->getUrl());
				if($e->getColour() !== null) $de->setColor($e->getColour());
				if($e->getAuthor()->getName() !== null) $de->setAuthor($e->getAuthor()->getName(), $e->getAuthor()->getIconUrl()??"", $e->getAuthor()->getUrl()??"");
				if($e->getThumbnail()->getUrl() !== null) $de->setThumbnail($e->getThumbnail()->getUrl());
				if($e->getImage()->getUrl() !== null) $de->setImage($e->getImage()->getUrl());
				if($e->getDescription() !== null) $de->setDescription($e->getDescription());
				if($e->getFooter()->getText() !== null) $de->setFooter($e->getFooter()->getText(), $e->getFooter()->getIconUrl()??"");
				if($e->getTimestamp() !== null) $de->setTimestamp($e->getTimestamp());
				foreach($e->getFields() as $f){
					$de->addFieldValues($f->getName(), $f->getValue(), $f->isInline());
				}
			}
			if($m instanceof Reply){
				if($m->getReferencedMessageId() === null){
					$this->resolveRequest($pk->getUID(), false, "Failed to send.", ["Reply message has no referenced message ID."]);
					MainLogger::getLogger()->debug("Failed to send message ({$pk->getUID()}) - Reply message has no referenced message ID.");
					return;
				}
				$this->getMessage($pk, $m->getChannelId(), $m->getReferencedMessageId(), function(DiscordMessage $msg) use($channel, $pk, $de){
					$channel->sendMessage($pk->getMessage()->getContent(), false, $de, null, $msg)->done(function(DiscordMessage $msg) use($pk){
						$this->resolveRequest($pk->getUID(), true, "Message sent.", [ModelConverter::genModelMessage($msg)]);
						MainLogger::getLogger()->debug("Sent message ({$pk->getUID()})");
					}, function(\Throwable $e) use($pk){
						$this->resolveRequest($pk->getUID(), false, "Failed to send.", [$e->getMessage(), $e->getTraceAsString()]);
						MainLogger::getLogger()->debug("Failed to send message ({$pk->getUID()}) - {$e->getMessage()}");
					});
				});
			}else{
				$channel->sendMessage($m->getContent(), false, $de)->done(function(DiscordMessage $msg) use ($pk){
					$this->resolveRequest($pk->getUID(), true, "Message sent.", [ModelConverter::genModelMessage($msg)]);
					MainLogger::getLogger()->debug("Sent message ({$pk->getUID()})");
				}, function(\Throwable $e) use ($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to send.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to send message ({$pk->getUID()}) - {$e->getMessage()}");
				});
			}
		});
	}

	private function handleEditMessage(RequestEditMessage $pk): void{
		$message = $pk->getMessage();
		if($message->getId() === null){
			$this->resolveRequest($pk->getUID(), false, "No message ID provided.");
			return;
		}
		$this->getMessage($pk, $message->getChannelId(), $message->getId(), function(DiscordMessage $dMessage) use($pk, $message){
			$e = $pk->getMessage()->getEmbed();
			$de = null;
			if($e !== null){
				$de = new DiscordEmbed($this->client->getDiscordClient());
				if($e->getType() !== null) $de->setType($e->getType());
				if($e->getTitle() !== null) $de->setTitle($e->getTitle());
				if($e->getUrl() !== null) $de->setURL($e->getUrl());
				if($e->getColour() !== null) $de->setColor($e->getColour());
				if($e->getAuthor()->getName() !== null) $de->setAuthor($e->getAuthor()->getName(), $e->getAuthor()->getIconUrl()??"", $e->getAuthor()->getUrl()??"");
				if($e->getThumbnail()->getUrl() !== null) $de->setThumbnail($e->getThumbnail()->getUrl());
				if($e->getImage()->getUrl() !== null) $de->setImage($e->getImage()->getUrl());
				if($e->getDescription() !== null) $de->setDescription($e->getDescription());
				if($e->getFooter()->getText() !== null) $de->setFooter($e->getFooter()->getText(), $e->getFooter()->getIconUrl()??"");
				if($e->getTimestamp() !== null) $de->setTimestamp($e->getTimestamp());
				foreach($e->getFields() as $f){
					$de->addFieldValues($f->getName(), $f->getValue(), $f->isInline());
				}
			}
			$dMessage->content = $message->getContent();
			if($de !== null){
				$dMessage->embeds->clear();
				$dMessage->addEmbed($de);
			}
			$dMessage->channel->messages->save($dMessage)->done(function(DiscordMessage $dMessage) use($pk){
				$this->resolveRequest($pk->getUID(), true, "Message edited.", [ModelConverter::genModelMessage($dMessage)]);
			}, function(\ThreadException $e) use ($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to edit message.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to edit message ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleDeleteMessage(RequestDeleteMessage $pk): void{
		$this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $dMessage) use($pk){
			$dMessage->delete()->done(function() use ($pk){
				$this->resolveRequest($pk->getUID());
			}, function(\ThreadException $e) use ($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to delete message.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to delete message ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleKickMember(RequestKickMember $pk): void{
		$this->getMember($pk, $pk->getServerId(), $pk->getUserId(), function(DiscordMember $member, DiscordGuild $guild) use($pk){
			$guild->members->kick($member)->then(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Member kicked.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to kick member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to kick member ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleInitialiseBan(RequestInitialiseBan $pk): void{
		$this->getServer($pk, $pk->getBan()->getServerId(), function(DiscordGuild $guild) use($pk){
			$guild->bans->ban($pk->getBan()->getUserId(), $pk->getBan()->getDaysToDelete(), $pk->getBan()->getReason())->then(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Member banned.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to ban member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to ban member ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleRevokeBan(RequestRevokeBan $pk): void{
		$this->getServer($pk, $pk->getServerId(), function(DiscordGuild $guild) use($pk){
			$guild->unban($pk->getUserId())->then(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Member unbanned.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to unban member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to unban member ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleInitialiseInvite(RequestInitialiseInvite $pk): void{
		$invite = $pk->getInvite();
		$this->getChannel($pk, $invite->getChannelId(), function(DiscordChannel $channel) use($pk, $invite){
			/** @phpstan-ignore-next-line Poorly documented function on discord.php's side. */
			$channel->createInvite([
				"max_age" => $invite->getMaxAge(), "max_uses" => $invite->getMaxUses(), "temporary" => $invite->isTemporary(), "unique" => true
			])->done(function(DiscordInvite $dInvite) use($pk){
				$this->resolveRequest($pk->getUID(), true, "Invite initialised.", [ModelConverter::genModelInvite($dInvite)]);
				MainLogger::getLogger()->debug("Invite initialised ({$pk->getUID()})");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to initialise.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to initialise invite ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleRevokeInvite(RequestRevokeInvite $pk): void{
		$this->getServer($pk, $pk->getServerId(), function(DiscordGuild $guild) use($pk){
			$guild->invites->freshen()->done(function(DiscordInviteRepository $invites) use($pk){
				/** @var DiscordInvite $dInvite */
				$dInvite = $invites->offsetGet($pk->getInviteCode());
				$invites->delete($dInvite)->done(function(DiscordInvite $dInvite) use($pk){
					$this->resolveRequest($pk->getUID(), true, "Invite revoked.", [ModelConverter::genModelInvite($dInvite)]);
					MainLogger::getLogger()->debug("Invite revoked ({$pk->getUID()})");
				}, function(\Throwable $e) use($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to revoke.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to revoke invite ({$pk->getUID()}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to freshen invites.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to revoke invite ({$pk->getUID()}) - invite freshen error: {$e->getMessage()}");
			});
		});
	}

	//---------------------------------------------------

	private function getServer(Packet $pk, string $server_id, callable $cb): void{
		$this->client->getDiscordClient()->guilds->fetch($server_id)->done(function(DiscordGuild $guild) use($cb){
			$cb($guild);
		}, function(\Throwable $e) use($pk){
			$this->resolveRequest($pk->getUID(), false, "Failed to fetch server.", [$e->getMessage(), $e->getTraceAsString()]);
			MainLogger::getLogger()->debug("Failed to process request (".get_class($pk)."|{$pk->getUID()}) - server error: {$e->getMessage()}");
		});
	}

	//Includes DM Channels.
	private function getChannel(Packet $pk, string $channel_id, callable $cb): void{
		$c = $this->client->getDiscordClient()->getChannel($channel_id);
		if($c === null){
			/** @var DiscordUser|null $u */
			$u = $this->client->getDiscordClient()->users->offsetGet($channel_id);
			if($u === null){
				$this->resolveRequest($pk->getUID(), false, "Failed to find channel/user.", ["Failed to find channel from local storage."]);
				MainLogger::getLogger()->debug("Failed to process request (".get_class($pk)."|{$pk->getUID()}) - channel error: Failed to find channel from local storage.");
			}else{
				$u->getPrivateChannel()->then(function(DiscordChannel $channel) use($cb){
					$cb($channel);
				}, function(\Throwable $e) use($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to fetch private channel..", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to process request (".get_class($pk)."|{$pk->getUID()}) - private channel error: {$e->getMessage()}");
				});
			}
		}else{
			$cb($c);
		}
	}

	private function getMessage(Packet $pk, string $channel_id, string $message_id, callable $cb): void{
		$this->getChannel($pk, $channel_id, function(DiscordChannel $channel) use($pk, $message_id, $cb){
			$channel->messages->fetch($message_id)->done(function(DiscordMessage $dMessage) use ($cb){
				$cb($dMessage);
			}, function(\Throwable $e) use ($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to fetch message.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to process request (".get_class($pk)."|{$pk->getUID()}) - message error: {$e->getMessage()}");
			});
		});
	}

	private function getMember(Packet $pk, string $server_id, string $user_id, callable $cb): void{
		$this->getServer($pk, $server_id, function(DiscordGuild $guild) use($pk, $user_id, $cb){
			$guild->members->fetch($user_id)->then(function(DiscordMember $member) use($guild, $cb){
				$cb($member, $guild);
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to fetch member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to process request (".get_class($pk)."|{$pk->getUID()}) - member error: {$e->getMessage()}");
			});
		});
	}

	//---------------------------------------------------

	private function resolveRequest(int $pid, bool $successful = true, string $response = "Success.", array $data = []): void{
		$pk = new Resolution($pid, $successful, $response, $data);
		$this->client->getThread()->writeOutboundData($pk);
	}

	public function sendHeartbeat(): void{
		$pk = new Heartbeat(microtime(true));
		$this->client->getThread()->writeOutboundData($pk);
	}

	public function checkHeartbeat(): void{
		if($this->lastHeartbeat === null) return;
		if(($diff = (microtime(true) - $this->lastHeartbeat)) > Protocol::HEARTBEAT_ALLOWANCE){
			MainLogger::getLogger()->emergency("Plugin has not responded for {$diff} seconds, closing thread.");
			$this->client->close();
		}
	}

	public function getLastHeartbeat(): ?float{
		return $this->lastHeartbeat;
	}
}