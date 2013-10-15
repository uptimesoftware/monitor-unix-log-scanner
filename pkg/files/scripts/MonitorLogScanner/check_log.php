<?php

require('rcs_function.php');

$debug = false;

// get all the variables from the environmental variable
$agent_hostname = getenv('UPTIME_HOSTNAME');
$agent_port     = getenv('UPTIME_PORT');
$log_file       = getenv('UPTIME_LOG_FILE');
$line_num       = getenv('UPTIME_LINE_NUM');
$search_regex   = getenv('UPTIME_SEARCH_REGEX');
$ignore_regex   = getenv('UPTIME_IGNORE_REGEX');
$use_bookmark   = getenv('UPTIME_USEBOOKMARKFILE');

if ($use_bookmark) {
	// strip away any invalid characters to use for the bookmark file name
	$pattern = '/[\/\\\?\'\"\:\;\*\^\$\@\!\#\%]/';
	$f = preg_replace($pattern, '', $log_file);
	$tmpfile = "tmpfile-{$agent_hostname}-{$f}.last";
	$skip_lines = false;

	// open the last line in the bookmark file (if it exists)
	$prev_line = '';
	if (file_exists($tmpfile)) {
		$fh = fopen($tmpfile, 'r');
		$prev_line = fgets($fh);
		fclose($fh);
		$skip_lines = true;
	}
}
	
// Main code

$cmd = "tailvaradm {$line_num} {$log_file}";
if ($debug) {
	print "Hostname: {$agent_hostname}\n";
	print "Port: {$agent_port}\n";
	print "Cmd: {$cmd}\n";
}
$agent_output = agentcmd($agent_hostname, $agent_port, $cmd);

if (strlen($agent_output) == 0) {
	print "Error No lines returned from agent. Check the agent script permissions?";
	exit(1);
}
if (stristr($agent_output, "ERR")) {
	print "Error Output received: 'ERR'. The agent may not be configured correctly. Check the password?";
	exit(1);
}
if (stristr($agent_output, "tail: cannot open `{$log_file}' for reading:")) {
	print "Error Cannot open log/text file for reading: '{$log_file}'";
	exit(1);
}

// scan output for the search string
$output_arr = preg_split("/\n/", $agent_output);
if ($debug) {
	print_r($output_arr);
}
$occurences = 0;
$last_line = '';
$i = 0;
while ($i < count($output_arr) && $skip_lines) {
	$line = $output_arr[$i];
	// check for empty string
	if (strlen($line) > 0) {
		// skip all lines until we find the last line (if it's not empty)
		if ($line == $prev_line) {
			$i++;
			break;
		}
	}
	$i++;
}
// if we didn't find the string, assume the log was rotated, so we'll start from the beginning
if ($i >= count($output_arr)) {
	$i = 0;
}

while ($i < count($output_arr)) {
	$line = $output_arr[$i];
	// check for empty string
	if (strlen($line) > 0) {
		if (! preg_match("/" . $ignore_regex . "/", $line) || strlen($ignore_regex) == 0) {
			if (preg_match("/" . $search_regex . "/", $line)) {
				$occurences++;
				print "Output {$line}\n";
			}
		}
		$last_line = $line;
	}
	$i++;
}

// print results
print "occurences {$occurences}\n";

if ($use_bookmark) {
	// save the last line in the bookmark file
	if (strlen($last_line) > 0) {
		$fh = fopen($tmpfile, 'w');
		fwrite($fh, $last_line);
		fclose($fh);
	}
}
?>
