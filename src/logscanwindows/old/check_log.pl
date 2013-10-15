#!/usr/bin/perl
use File::Spec::Functions;
use Algorithm::Diff qw(diff);

$FILENAME=$ENV{UPTIME_LOG_FILE};

# get rid of \ / : from filename

$FILENAME =~ s/\/|\\|\:/_/g;
$TEMPDIR="..\\..\\tmp";
if (! -d $TEMPDIR)
{
`mkdir $TEMPDIR`
}
$TMPFILE="$TEMPDIR\\$ENV{UPTIME_HOSTNAME}.$FILENAME.$$";
$TMPPREV="$TEMPDIR\\$ENV{UPTIME_HOSTNAME}.$FILENAME.prev";

$HOSTNAME= shift || $ENV{UPTIME_HOSTNAME} || "" ;
$PORT= shift || $ENV{UPTIME_PORT} || "" ;
$LOGCHK = shift || $ENV{UPTIME_LOG_FILE} || "" ;
$FACILITY= shift || $ENV{UPTIME_FACILITY} || "" ;
$LEVEL = shift || $ENV{UPTIME_LEVEL} || "" ;
$NUMLINES = shift || $ENV{UPTIME_LINES} || "" ;
$TEXT_JOIN = shift || $ENV{UPTIME_TEXT} || "";

$CNT=0;

chomp($LOGCHK);

if ( $FACILITY  &&  $LEVEL )
{
    $TEXT_JOIN = ".*($FACILITY\.$LEVEL).*($TEXT_JOIN).*";
}

if ( $FACILITY && ! $LEVEL)
{
    $TEXT_JOIN = ".*($FACILITY\.).*($TEXT_JOIN).*";
}

if ( $LEVEL && ! $FACILITY)
{
    $TEXT_JOIN = ".*(\.$LEVEL).*($TEXT_JOIN).*";
}

if ( ! $HOSTNAME)
{
  print "WNG - no hostname specified\n";
  exit 1;
}

if ( ! $PORT)
{
  print "WNG - no port specified\n";
  exit 1;
}

if ( ! $NUMLINES )
{
  print "WNG - no lines specified\n";
  exit 1;
}

if ( ! $LOGCHK )
{
  print "WNG - no log specified \n";
  exit 1;
}

if ( ! $TEXT_JOIN  )
{
  print "WNG - no text to look for specified\n";
  exit 1;
}

`..\\agentcmd -p $PORT $HOSTNAME tailvaradm $NUMLINES $LOGCHK > $TMPFILE`;

if ( ! -e "$TMPFILE" )
{
  print "CRIT - Temporary File Not Found\n";	
	exit 2;
} 
if ( -e $TMPPREV )
{
open (F1, $TMPPREV) or die("Couldn't open $TMPPREV: $!");
}
open (F2, $TMPFILE) or die("Couldn't open $TMPFILE: $!");
chomp(@f1 = <F1>);
close F1;
chomp(@f2 = <F2>);
close F2;

$diffs = diff(\@f1, \@f2);


foreach $chunk (@$diffs) {
  
  foreach $line (@$chunk) {
    my ($sign, $lineno, $text) = @$line;
    if ($sign =~ /\+/)
    {
    if ( $text =~ /$TEXT_JOIN/ ) { $CNT++; $OUTPUT="$text";}
    }
  }
}

unlink($TMPPREV);
rename($TMPFILE,$TMPPREV);

print "found $CNT\n";
print "OUTPUT $OUTPUT";

exit 0;

# -- The End.
