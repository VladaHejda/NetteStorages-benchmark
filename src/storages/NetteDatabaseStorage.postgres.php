<?php

require __DIR__ . '/config/.postgres.config.php';

$connection = new \Nette\Database\Connection("pgsql:host=$host;dbname=$database", $login, $password);
$context = new \Nette\Database\Context($connection, new \Nette\Database\Reflection\DiscoveredReflection($connection));

$tempTable = 'benchmark_postgres_cache';
$context->query("
	CREATE TABLE $tempTable (
		key BIGINT NOT NULL,
		value TEXT NOT NULL,
		PRIMARY KEY (key)
	);
");

benchmark(new \Nette\Caching\Storages\DatabaseStorage($context, $tempTable));

$context->query("DROP TABLE $tempTable");
