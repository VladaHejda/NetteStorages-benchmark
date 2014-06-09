<?php

function benchmark(\Nette\Caching\IStorage $storage)
{
	global $keys, $loops, $traceErrors;

	ob_start();

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

	ob_end_flush();
}
