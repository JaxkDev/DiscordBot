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

namespace JaxkDev\DiscordBot\Bot;

use Discord\Discord;
use Discord\Exceptions\IntentException;
use Discord\WebSockets\Intents;
use Error;
use ErrorException;
use JaxkDev\DiscordBot\Bot\Handlers\DiscordEventHandler;
use JaxkDev\DiscordBot\Bot\Handlers\CommunicationHandler;
use JaxkDev\DiscordBot\Communication\BotThread;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Protocol;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use pocketmine\utils\MainLogger;
use React\EventLoop\TimerInterface;
use Throwable;

class Client{

	/** @var BotThread */
	private $thread;

	/** @var Discord */
	private $client;

	/** @var CommunicationHandler */
	private $communicationHandler;

	/** @var DiscordEventHandler */
	private $discordEventHandler;

	/** @var TimerInterface|null */
	private $readyTimer;
	/** @var TimerInterface|null */
	private $tickTimer;

	/** @var int */
	private $tickCount;
	/** @var int */
	private $lastGCCollection = 0;

	/** @var array */
	private $config;

	public function __construct(BotThread $thread, array $config){
		$this->thread = $thread;
		$this->config = $config;

		gc_enable();

		error_reporting(E_ALL & ~E_NOTICE);
		set_error_handler([$this, 'sysErrorHandler']);
		register_shutdown_function([$this, 'close']);

		// Mono logger can have issues with other timezones, for now use UTC.
		// Must be set globally due to internal methods in the rotating file handler.
		// Note, this does not effect outside thread config.
		ini_set("date.timezone", "UTC");
		MainLogger::getLogger()->debug("DiscordBot logs will be in UTC timezone.");

		Packet::$UID_COUNT = 1;

		$logger = new Logger('DiscordPHP');
		$handler = new RotatingFileHandler(\JaxkDev\DiscordBot\DATA_PATH.$config['logging']['directory'].DIRECTORY_SEPARATOR."DiscordBot.log", $config['logging']['maxFiles'], Logger::DEBUG);
		$handler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
		$logger->setHandlers(array($handler));

		if($config['logging']['debug']){
			//Note not thread safe on the output could mix and match between servers synced output and this debug output.
			$handler = new StreamHandler(($r = fopen('php://stdout', 'w')) === false ? "" : $r);
			$logger->pushHandler($handler);
		}

		$intents = [
			Intents::GUILDS,
			Intents::GUILD_MEMBERS,
			Intents::GUILD_BANS,
			Intents::GUILD_INVITES,
			Intents::GUILD_PRESENCES,
			Intents::GUILD_MESSAGES,
			//Intents::GUILD_VOICE_STATES, For voice state update
			Intents::DIRECT_MESSAGES
		];

		$socket_opts = [];
		if($config["discord"]["usePluginCacert"]){
			MainLogger::getLogger()->debug("TLS cafile set to '".\JaxkDev\DiscordBot\DATA_PATH."cacert.pem"."'");
			$socket_opts["tls"] = [
				"cafile" => \JaxkDev\DiscordBot\DATA_PATH."cacert.pem"
			];
		}

		try{
			$this->client = new Discord([
				'token' => $config['discord']['token'],
				'logger' => $logger,
				'socket_options' => $socket_opts,
				'loadAllMembers' => true,
				'storeMessages' => true,
				'intents' => $intents
			]);
		}catch(IntentException $e){
			$this->close($e);
		}

		$this->config['discord']['token'] = "REDACTED";

		$this->communicationHandler = new CommunicationHandler($this);
		$this->discordEventHandler = new DiscordEventHandler($this);

		$this->registerHandlers();
		$this->registerTimers();

		if($this->thread->getStatus() === Protocol::THREAD_STATUS_STARTING){
			$this->thread->setStatus(Protocol::THREAD_STATUS_STARTED);
			$this->client->run();
		}else{
			MainLogger::getLogger()->warning("Closing thread, unexpected state change.");
			$this->close();
		}
	}

	private function registerTimers(): void{
		// Handles shutdown, rather than a SHUTDOWN const to send through internal communication, set flag to closed.
		// Saves time & will guarantee closure ASAP rather then waiting in line through ^
		$this->client->getLoop()->addPeriodicTimer(1, function(){
			if($this->thread->getStatus() === Protocol::THREAD_STATUS_CLOSING){
				$this->close();
			}
		});

		// Handles any problems pre-ready.
		$this->readyTimer = $this->client->getLoop()->addTimer(30, function(){
			if($this->client->id !== null){
				MainLogger::getLogger()->warning("Client has taken >30s to get ready, How large is your discord server !?  [Create an issue on github is this persists]");
				$this->client->getLoop()->addTimer(30, function(){
					if($this->thread->getStatus() !== Protocol::THREAD_STATUS_READY){
						MainLogger::getLogger()->critical("Client has taken too long to become ready, shutting down.");
						$this->close();
					}
				});
			}else{
				//Should never happen unless your internet speed is like <10kb/s
				MainLogger::getLogger()->critical("Client failed to login/connect within 30 seconds, See log file for details.");
				$this->close();
			}
		});

		$this->tickTimer = $this->client->getLoop()->addPeriodicTimer(1/20, function(){
			// Note this is not accurate/fixed dynamically to 1/20th of a second.
			$this->tick();
		});
	}

	/** @noinspection PhpUnusedParameterInspection */
	private function registerHandlers(): void{
		// https://github.com/teamreflex/DiscordPHP/issues/433
		// Note ready is emitted after successful connection + all servers/users loaded, so only register events
		// After this event.
		$this->client->on('ready', function(Discord $discord){
			if($this->readyTimer !== null){
				$this->client->getLoop()->cancelTimer($this->readyTimer);
				$this->readyTimer = null;
			}
			$this->discordEventHandler->onReady();
		});

		$this->client->on('error', [$this, 'discordErrorHandler']);
		$this->client->on('closed', [$this, 'close']);
	}

	public function tick(): void{
		$data = $this->thread->readInboundData(Protocol::PACKETS_PER_TICK);

		foreach($data as $d){
			$this->communicationHandler->handle($d);
		}

		if(($this->tickCount % 20) === 0){
			if($this->thread->getStatus() === Protocol::THREAD_STATUS_READY){
				$this->communicationHandler->checkHeartbeat();
				$this->communicationHandler->sendHeartbeat();
			}

			//GC Tests.
			if(microtime(true)-$this->lastGCCollection >= 6000){
				$cycles = gc_collect_cycles();
				$mem = round(gc_mem_caches()/1024, 3);
				MainLogger::getLogger()->debug("[GC] Claimed {$mem}kb and {$cycles} cycles.");
				$this->lastGCCollection = time();
			}
		}

		$this->tickCount++;
	}

	public function getThread(): BotThread{
		return $this->thread;
	}

	public function getDiscordClient(): Discord{
		return $this->client;
	}

	public function getCommunicationHandler(): CommunicationHandler{
		return $this->communicationHandler;
	}

	public function sysErrorHandler(int $severity, string $message, string $file, int $line): bool{
		$this->close(new ErrorException($message, 0, $severity, $file, $line));
		return true;
	}

	/** @var array $data */
	public function discordErrorHandler(array $data): void{
		$this->close($data[0]??null);
	}

	public function close($error = null): void{ /** @phpstan-ignore-line  */
		if($this->thread->getStatus() === Protocol::THREAD_STATUS_CLOSED) return;
		$this->thread->setStatus(Protocol::THREAD_STATUS_CLOSED);
		if($error instanceof Throwable){
			MainLogger::getLogger()->logException($error);
		}
		if($this->client instanceof Discord){
			try{
				$this->client->close(true);
			}catch (Error $e){
				MainLogger::getLogger()->debug("Failed to close client, probably not started.");
			}
		}
		MainLogger::getLogger()->debug("Client closed.");
		exit(0);
	}
}