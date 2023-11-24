<?php

namespace easyNewsletter;

class farnLog{

	/**
	 * Logs a message with a timestamp into a farnLog.txt file.
	 * @param String $logEntry The message to be logged.
	 *
	 * @return void
	 */
    public static function log(String $logEntry): void
    {
        $handle = fopen(__DIR__."/farnLog.txt", "a");
        error_log("DEBUG INFO: PATH:".__DIR__."/farnLog.txt");
        fwrite($handle, gmdate('d-m-y h:i:s'). ": ".$logEntry  . "\n");
        fclose($handle);
    }
}