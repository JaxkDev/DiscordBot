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

use JaxkDev\DiscordBot\Communication\Packets\Resolution;
use JaxkDev\DiscordBot\Libs\React\Promise\Deferred;
use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;

/**
 * @internal
 */
abstract class ApiResolver{

	/** @var Array<int, Deferred> */
	static private $map = [];

	static public function create(int $uid): PromiseInterface{
		if(isset(self::$map[$uid])){
			throw new \AssertionError("Packet {$uid} already linked to a promise resolver.");
		}
		$d = new Deferred();
		self::$map[$uid] = $d;
		return $d->promise();
	}

	static public function getPromise(int $uid): ?PromiseInterface{
		return isset(self::$map[$uid]) ? self::$map[$uid]->promise() : null;
	}

	static public function handleResolution(Resolution $packet): void{
		if(isset(self::$map[$packet->getPid()])){
			$d = self::$map[$packet->getPid()];
			if($packet->wasSuccessful()){
				$d->resolve(new ApiResolution([$packet->getResponse(), ...$packet->getData()]));
			}else{
				$d->reject(new ApiRejection($packet->getResponse(), $packet->getData()));
			}
			unset(self::$map[$packet->getPid()]);
		}
	}
}