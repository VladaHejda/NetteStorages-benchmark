<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "\n";

$longOptions = ['help'];
$options = getopt('ctsl:', $longOptions);


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


// OPTIONS
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


// EXTERNALS
require_once __DIR__ . '/benchmark.php';
require_once __DIR__ . '/config.php';
if (!isset($storageInitializers) || !is_array($storageInitializers)) {
	die ('Please, initialize $storageInitializers array in config.php.');
}
if ($shuffleStorages) {
	shuffle($storageInitializers);
}

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


// TESTING DATA
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


// TEST
foreach ($storageInitializers as $initializer) {
	include $initializer;
}
