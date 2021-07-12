<?php

/*
 * https://github.com/pmmp/DevTools/blob/0c46527bee72324e5fee0c4ed2c7f5a324b6a4d0/src/DevTools/ConsoleScript.php#L62
 */

declare(strict_types=1);

echo "Installing dependencies, Removing any dev-dependencies & Optimising Autoloader...\n";

passthru("composer install --no-dev -o");

echo "Building plugin...\n";

/** @phpstan-ignore-next-line */
$basePath = rtrim(realpath(__DIR__), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

$includedPaths = array_map(function($path) : string{
	return rtrim(str_replace("/", DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
}, ['src','vendor','resources']);

$excludedPaths = [
	".md",  // Nobody will look at readme's etc in a phar...
	"carbon\\lang\\",  // More useless crap not needed and wasting ridiculous amount of space.
	"discord-php\\bin\\",  // VC Binaries, not needed.
	"vendor\\bin"
];

$metadata = generatePluginMetadataFromYml($basePath."plugin.yml");
if($metadata === null){
	echo "Missing entry point or plugin.yml".PHP_EOL;
	exit(1);
}

$name = ((!($opt = getopt("o:")) || $opt['o'] === false) ? str_replace(".","_",("DiscordBot_v".$metadata['version'])).".phar" : $opt["o"]);

if (!is_dir("dist")) mkdir("dist");

foreach(buildPhar(__DIR__.DIRECTORY_SEPARATOR."dist".DIRECTORY_SEPARATOR.$name, $basePath, $includedPaths, $excludedPaths, $metadata, "<?php __HALT_COMPILER();") as $line){
	echo $line.PHP_EOL;
}

/**
 * @param string[]    $strings
 * @param string|null $delim
 *
 * @return string[]
 */
function preg_quote_array(array $strings, string $delim = null) : array{
	return array_map(function(string $str) use ($delim) : string{ return preg_quote($str, $delim); }, $strings); /** @phpstan-ignore-line  */
}

/**
 * @param string $pharPath
 * @param string $basePath
 * @param string[] $includedPaths
 * @param string[] $excludedPaths
 * @param array $metadata
 * @param string $stub
 * @param int $signatureAlgo
 *
 * @return Generator|string[]
 */
function buildPhar(string $pharPath, string $basePath, array $includedPaths, array $excludedPaths, array $metadata, string $stub, int $signatureAlgo = Phar::SHA1){
	if(file_exists($pharPath)){
		yield "Phar file already exists, overwriting...";
		try{
			Phar::unlinkArchive($pharPath);
		} /** @noinspection PhpRedundantCatchClauseInspection */ catch(PharException $e){
			//unlinkArchive() doesn't like dodgy phars
			unlink($pharPath);
		}
	}

	yield "Output File: $pharPath";

	yield "Adding files...";

	$start = microtime(true);
	$phar = new Phar($pharPath);
	$phar->setMetadata($metadata);
	$phar->setStub($stub);
	$phar->setSignatureAlgorithm($signatureAlgo);
	$phar->startBuffering();

	/** @phpstan-ignore-next-line */
	$excludedSubstrings = preg_quote_array(array_merge([
		realpath($pharPath) //don't add the phar to itself
	], $excludedPaths), '/');

	$folderPatterns = preg_quote_array([
		DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR,
		DIRECTORY_SEPARATOR.'.' //"Hidden" files, git dirs etc
	], '/');

	$basePattern = preg_quote(rtrim($basePath, DIRECTORY_SEPARATOR), '/');
	foreach($folderPatterns as $p){
		$excludedSubstrings[] = $basePattern.'.*'.$p;
	}

	$regex = sprintf('/^(?!.*(%s))^%s(%s).*/i',
		implode('|', $excludedSubstrings),
		preg_quote($basePath, '/'),
		implode('|', preg_quote_array($includedPaths, '/'))
	);

	$directory = new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::CURRENT_AS_PATHNAME);
	$iterator = new RecursiveIteratorIterator($directory);
	$regexIterator = new RegexIterator($iterator, $regex);

	$count = count($phar->buildFromIterator($regexIterator, $basePath))+2;
	$phar->addFile("plugin.yml");
	$phar->addFile("LICENSE");
	yield "Added $count files, Compressing...";

	$phar->compressFiles(Phar::GZ);

	yield "Compressed";

	$phar->stopBuffering();

	yield "Done in ".round(microtime(true) - $start, 3)."s";
}

/**
 * @param string $pluginYmlPath
 * @return mixed[]|null
 * @phpstan-return array<string, mixed>|null
 */
function generatePluginMetadataFromYml(string $pluginYmlPath) : ?array{
	if(!file_exists($pluginYmlPath)){
		return null;
	}

	$pluginYml = yaml_parse_file($pluginYmlPath);
	return [
		"name" => $pluginYml["name"],
		"version" => $pluginYml["version"],
		"main" => $pluginYml["main"],
		"api" => $pluginYml["api"],
		"depend" => $pluginYml["depend"] ?? "",
		"description" => $pluginYml["description"] ?? "",
		"author" => $pluginYml["author"] ?? "", //Gotta love shoghi...
		"authors" => $pluginYml["authors"] ?? "",
		"website" => $pluginYml["website"] ?? "",
		"creationDate" => time()
	];
}
