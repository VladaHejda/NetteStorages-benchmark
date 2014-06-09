<?php

require __DIR__ . '/config/.mysql.config.php';

$connection = new \Nette\Database\Connection("mysql:host=$host;dbname=$database", $login, $password);
$context = new \Nette\Database\Context($connection, new \Nette\Database\Reflection\DiscoveredReflection($connection));

$tempTable = 'benchmark_mysql_cache';
$context->query("
	CREATE TABLE $tempTable (
		`key` BIGINT NOT NULL,
		value BLOB NOT NULL,
		PRIMARY KEY (`key`)
	);
");

benchmark(new \Nette\Caching\Storages\DatabaseStorage($context, $tempTable));

$context->query("DROP TABLE $tempTable");
