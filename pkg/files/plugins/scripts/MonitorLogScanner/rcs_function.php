<?php

// Remote Custom Script
// This script just opens a TCP socket to the agent and writes and reads some text output to/from the agent
// Written in PHP to get around any Netcat antivirus false-positive issues
// Joel Pereira

// Options:
// -h hostname
// -p port
// -s security password
// -c command/remote scripts
// -a arguments to send to script

function uptime_remote_custom_monitor($host, $port, $pass, $cmd, $args) {
	return agentcmd($host, $port, "rexec {$pass} {$cmd} {$args}");
}

function agentcmd($host, $port, $cmd, $timeout = 55) {
	$errorno = '';
	$errorstr = '';
	$rv = '';

	try {
		$resource = fsockopen($host, $port, $errorno, $errorstr, $timeout);
		//Attempt to establish a connection to agent on port 9998. On error, place the error number into $errorno, and a string response to $errorstr. Timeout after 10 seconds.
		if (!$resource) {
			//fsockopen failed
			echo "No connection established. Error: " . $errorstr . "[" . $errorno . "]\n";
		} else {
			// successfully opened a socket
			fwrite($resource, $cmd);
			//while there is data to read from $resource…
			while (!feof($resource)) {
				//read the data, 2048 bytes at a time and echo it out to stdout
				$rv .= fgets($resource, 2048);
			}
			//no more data to read, close the resource
			fclose($resource);
		}
	} catch (Exception $e) {
		print "Error:";
		var_dump($e->getMessage());
	}
	return $rv;
}
?>