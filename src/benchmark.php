<?php

echo "\n";

$longOptions = ['help'];
$options = getopt('ctsl:', $longOptions);

$colorOutput = isset($options['c']);
$traceErrors = isset($options['t']);
$shuffleStorages = isset($options['s']);
$defaultLoops = 100;
if (isset($options['l'])) {
	$loops = (int) $options['l'];
	if ($loops < 1) {
		echo red("Loops must be positive integer.\n");
		die;
	}
} else {
	$loops = $defaultLoops;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

if ($colorOutput) {
	require_once __DIR__ . '/color.php';
	require_once __DIR__ . '/color_shortcuts.php';
} else {
	// dummy
	function color($string) {
		return $string;
	}
	require_once __DIR__ . '/color_shortcuts.php';
}


// HELP
if (isset($options['help'])) {
	echo cyan("Nette storages benchmark.\n")
		. "Usage:\n\n"
		. yellow('-c') . "             colored output\n"
		. yellow('-l <integer>') . "   test loops count (higher number, longer execution; default $defaultLoops)\n"
		. yellow('-s') . "             shuffle storage test order\n"
		. yellow('-t') . "             print trace on error\n"
		. yellow('--help') . "         this help screen\n"
	;
	die;
}


if (!isset($storageInitializers) || !is_array($storageInitializers)) {
	die ('Please, initialize $storageInitializers array in config.php.');
}

$keys = [];
$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$max = strlen($chars) -1;
for ($i = 0; $i < 10; $i++) {
	$key = '';
	for ($j = 0; $j < 10; $j++) {
		$key .= $chars[rand(0, $max)];
	}
	$keys[] = $key;
}
$keys = range('a', 'z');

set_error_handler(function($code, $message, $file, $line){
	throw new ErrorException($message, $code, 1, $file, $line);
});

if ($shuffleStorages) {
	shuffle($storageInitializers);
}

ob_start();
foreach ($storageInitializers as $initializer) {

	$storage = include $initializer;

	if ($storage === NULL) {
		echo red("Initializer '$initializer' must return Nette\\Caching\\IStorage.\n");
		continue;

	} elseif (!$storage instanceof \Nette\Caching\IStorage) {
		echo red("Initializer '$initializer' did not returned Nette\\Caching\\IStorage instance.\n");
	}


	/**********TEST**********/

	$start = microtime(TRUE);
	try {
		$written = FALSE;
		for ($i = 0; $i < $loops; $i++) {
			foreach ($keys as $key) {
				$value = $storage->read($key);
				if ($written && $value !== md5($key)) {
					throw new ErrorException(get_class($storage) . " failed - returned wrong value for key $key.");
				}
				$storage->remove($key);
				$storage->write($key, md5($key), []);
			}
			$written = TRUE;
			if ($i && !$i % 3) {
				$written = FALSE;
				$storage->clean([\Nette\Caching\Cache::ALL => TRUE]);
			}
		}

		$duration = microtime(TRUE) - $start;
		echo yellow(get_class($storage) . ": $duration s.\n");

	} catch (Exception $e) {
		echo red ('Error (' . $e->getCode() . '): ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . "\n");
		if ($traceErrors) {
			echo $e->getTraceAsString() . "\n\n";
		}
	}

	ob_flush();

	/************************/
}

ob_end_clean();
