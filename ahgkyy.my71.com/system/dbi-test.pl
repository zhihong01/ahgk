#!/usr/bin/perl -w 
# DBI-Test.pl - DbMailAdministrator (DBMA) V2.4.x Copyright 2004-2006 **
#      Trouble? Contact: http://dbma.mobrien.com/DBMA_contact.htm
#################################################################
use strict;
use DBI;
print "DBI drivers:\n";
my @drivers = DBI->available_drivers('quiet');
my $driver;
foreach $driver (@drivers)
{
    print "$driver\n";
}
exit;


