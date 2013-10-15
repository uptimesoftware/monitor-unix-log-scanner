UNIX Log Scanner Plugin Monitor

Contents
1. About
2. Dependencies
3. Installation
4. Examples


1. About

This plugin monitor is designed for use with the Solaris and Linux up.time 5 monitoring stations with AIX, Linux and Solaris version 5 agents.

This custom monitor scans a particular log file on the agent system and searches the log file for a specified string.

Below is a description of each of the UNIX Log Scanner settings that are used to monitor the log file.
Port - port that the up.time monitoring station communicates with the agent on
Script Name - the location of the script on the monitoring station that contains the logic for contacting the agent and returning the  output in a useful format to the monitoring station.
Log File - the full path location of the log file on the agent system that is to be monitored
Facility Type - in addition to the search string, the search can be narrowed down by facility type (this field is not required)
Level - same as the facility type, you can further narrow the search based on the level of the log message (this field is not required)
# of Lines - the number of recent log entries that will be searched against
Text to Look for - the string to be searched for in the log file
Number of Times Found - these fields are used to set the alerting levels based on how many times the search string is found


2. Dependencies

at least version 5.x.x of the up.time agent
up.time 5 monitoring station
Perl (needs to be installed on the monitoring station server)

Here are some example settings for the UNIX Log Scanner monitor.

Search the last fifty lines of the /logging/program.log file for an entry of ERROR.  If it appears one or more times, send an alert.
Port - 9998
Script Name - scripts/check_log.pl
Log File - /logging/program.log
Facility Type - 
Level - 
# of Lines - 50
Text to Look for - ERROR
Number of Times Found - Critical: is greater than or equal to 1

Search the last one hundred lines of the /var/log/messages file for an entry of failed and was logged with the level crit.  If it appears one or more times, send an alert.
Port - 9998
Script Name - scripts/check_log.pl
Log File - /var/log/messages
Facility Type - 
Level - crit
# of Lines - 100
Text to Look for - failed
Number of Times Found - Critical: is greater than or equal to 1


Contact up.time Support at 416-868-0152 x2 if you have any problems or questions.
