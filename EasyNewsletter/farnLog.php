<?php

namespace EasyNewsletter;

class farnLog{

	/**
	 * Logs a message with a timestamp into a farnLog.txt file.
	 * @param String $logEntry The message to be logged.
	 *
	 * @return void
	 */
    public static function log(String $logEntry): void
    {
		$logLocation = wp_upload_dir()["basedir"] . "/farnLog.txt";

        $handle = fopen($logLocation, "a");
        error_log("DEBUG INFO: PATH: ".$logLocation);
        fwrite($handle, gmdate('d-m-y h:i:s'). ": ".$logEntry  . "\n");
        fclose($handle);
    }
}