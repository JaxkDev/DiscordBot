<?php /** @noinspection PhpUnusedPrivateMethodInspection */

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

abstract class ConfigUtils{

	const VERSION = 2;

	// Map all versions to a static function.
	private const PATCH_MAP = [
		1 => "patch_1"
	];

	static public function update(array &$config): void{
		for($i = (int)$config["version"]; $i < self::VERSION; $i += 1){
			$f = self::PATCH_MAP[$i];
			$config = forward_static_call([self::class, $f], $config);
		}
	}

	static private function patch_1(array $config): array{
		$config["version"] = 2;
		if(!isset($config["discord"])){
			$config["discord"] = [
				"token" => "Long token here",
				"usePluginCacert" => true
			];
		}else{
			$config["discord"]["usePluginCacert"] = true;
		}
		return $config;
	}

	/**
	 * Verifies the config's keys and values, returning any keys and a relevant message.
	 * @param array $config
	 * @return string[]
	 */
	static public function verify(array $config): array{
		$result = [];

		if(!array_key_exists("version", $config) or $config["version"] === null){
			$result[] = "No 'version' key found.";
		}else{
			if(!is_int($config["version"]) or $config["version"] <= 0 or $config["version"] > self::VERSION){
				$result[] = "Invalid 'version' ({$config["version"]}), you were warned not to touch it...";
			}
		}

		if(!array_key_exists("discord", $config) or $config["discord"] === null){
			$result[] = "No 'discord' key found.";
		}else{
			if(!array_key_exists("token", $config["discord"]) or $config["discord"]["token"] === null){
				$result[] = "No 'discord.token' key found.";
			}else{
				if(!is_string($config["discord"]["token"]) or strlen($config["discord"]["token"]) < 59){
					$result[] = "Invalid 'discord.token' ({$config["discord"]["token"]}), did you follow the wiki ?";
				}
			}
			if(!array_key_exists("usePluginCacert", $config["discord"]) or $config["discord"]["usePluginCacert"] === null){
				$result[] = "No 'discord.usePluginCacert' key found.";
			}else{
				if(!is_bool($config["discord"]["usePluginCacert"])){
					$result[] = "Invalid 'discord.usePluginCacert' ({$config["discord"]["usePluginCacert"]}), must be true or false";
				}
			}
		}

		if(!array_key_exists("logging", $config) or $config["logging"] === null){
			$result[] = "No 'logging' key found.";
		}else{
			if(!array_key_exists("debug", $config["logging"])  or $config["logging"]["debug"] === null){
				$result[] = "No 'logging.debug' value found.";
			}else{
				if(!is_bool($config["logging"]["debug"])){
					$result[] = "Invalid 'logging.debug' ({$config["logging"]["debug"]}), should be true or false.";
				}
			}

			if(!array_key_exists("maxFiles", $config["logging"]) or $config["logging"]["maxFiles"] === null){
				$result[] = "No 'logging.maxFiles' key found.";
			}else{
				if(!is_int($config["logging"]["maxFiles"]) or $config["logging"]["maxFiles"] <= 0){
					$result[] = "Invalid 'logging.maxFiles' ({$config["logging"]["maxFiles"]}), should be an int > 0.";
				}
			}

			if(!array_key_exists("directory", $config["logging"]) or $config["logging"]["directory"] === null){
				$result[] = "No 'logging.directory' key found.";
			}else{
				if(!is_string($config["logging"]["directory"]) or strlen($config["logging"]["directory"]) === 0){
					$result[] = "Invalid 'logging.directory' ({$config["logging"]["directory"]}).";
				}
			}
		}

		return $result;
	}
}