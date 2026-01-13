#!/usr/bin/perl -w

use LWP::Simple;
use DBI;
use strict;

my $db = "tt2";
my $db_user = "dow";
my $db_password = "8WWzzxbhv59mGPJT";

print "Connecting to database\n";
my $dbh = DBI->connect("DBI:mysql:database=$db;host=127.0.0.1",$db_user, $db_password,{'RaiseError' => 1});
print "Done\n";
my $sth = $dbh->prepare(qq{select tvdbid,name from series});
$sth->execute;

my @thearray = (); 
print "Executing database query\n";
while (my $ref = $sth->fetchrow_hashref()) {
	push(@thearray,$ref->{tvdbid});
}
print "Done\n";
$dbh->disconnect;

print "Fetching ".scalar(@thearray)." files\n";
foreach(@thearray) {
	print "Downloading $_\n";
	getstore "http://thetvdb.com/api/1DAE7A9823E16F0D/series/".$_."/all/en.zip", "/series/updates/zip/".$_.".zip";
	print "Done with $_.zip\n";
}

print "Done downloading all files\n";

print "Unzip all files\n";
#got all the zip files now unzip them
opendir DIRH, "/series/updates/zip" or die "couldn't open: $!";
foreach (sort readdir DIRH) {
my $filename = substr $_, 0, -4; #remove file extention .zip from filename
system("unzip -p /series/updates/zip/$_ en.xml > /series/updates/$filename.xml");
}
closedir DIRH;
print "Done unzipping all files\n";