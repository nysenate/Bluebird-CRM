===============================================================================
CacheRouter
$Id: README.txt,v 1.1.2.1 2009/09/05 13:24:50 slantview Exp $
===============================================================================

-------------------------------------------------------------------------------
- About -
-------------------------------------------------------------------------------

CacheRouter is a caching system for Drupal allowing you to assign individual
cache tables to specific cache technology.  CacheRouter also utilizes the
page_fast_cache part of Drupal in order to reduce the amount of resources
needed for serving pages to anonymous users.


-------------------------------------------------------------------------------
- Installation -
-------------------------------------------------------------------------------

1.  Enable the module in admin/build/modules.
2.  Setup your settings.php


-------------------------------------------------------------------------------
- Configuration -
-------------------------------------------------------------------------------

CacheRouter has some pretty sane defaults, and it usually won't hurt to leave
it in the default mode.  With that said there are a few tweaks that are 
critical especially if you are running multiple sites.

Add the following lines to your settings.php:

$conf['cache_inc'] = './sites/all/modules/contrib/cacherouter/cacherouter.inc';
$conf['cacherouter'] = array(
  'default' => array(
    'engine' => 'db',
    'server' => array(),
    'shared' => TRUE,
    'prefix' => '',
    'path' => 'sites/default/files/filecache',
    'static' => FALSE,
    'fast_cache' => TRUE,
  ),
);

default is for the default caching engine. All valid cache tables or "bins" can 
be added in addition, but you must have a default if you skip any bins.

For engine, the current available options are: apc, db, file, memcache and 
xcache.

server is only used in memcache and should be an array of host:port 
combinations. (e.g. 'server' => array('localhost:11211', 'localhost:11212'))

shared is only used on memcache as well. This allows memcache to be used with a
single process and still handle flushing correctly.

prefix is for unique site names usually when running multiple sites.

path is new in beta3 for 5.x and 6.x branches. It allows you to override the 
default of /tmp/filecache for storing files when using the "file" caching type. 
*update this now works as of beta8. Also note: when using this module with 
multi-site setups, you need to change this to point to the file cache for each
site. (e.g. sites/site1.com/files/filecache, sites/site2.com/files/filecache)
or you WILL have cache corruption.

fast_cache is new in beta8 for turning page_fast_cache on. WARNING: you will 
not get Anonymous statistics if you use this option. Please set it to FALSE if
you want to get Anonymous statistics.

static is new in beta8 for allowing a bin to keep a static array cache so 
multiple requests per page will not hit the remote cache. This defaults to 
FALSE due to the fact that in Drupal 6 there are several caches (menu, 
localization) that do their own static storage. Advanced feature, use at own 
discretion.

-------------------------------------------------------------------------------
- TODO -
-------------------------------------------------------------------------------

A few things I would like to have done.  

1.  Allow cache "chaining", for example, it would be nice to have default and a
"backup" cache so for any critical cache (not that there should be critical
caches), you could have memcache backed by db or file backed by db or memcache
backed by apc backed by db.  Not sure how I would like to implement this yet.

2.  I would love to see this tested on large production sites and work out the
bugs and get it into core.  I don't see why this caching shouldn't be part of
Drupal core.

3.  Any ideas for additional caching types.  

4.  I would love to get a really nice, clean web stats thing going with pretty
pictures and everything.


-------------------------------------------------------------------------------
- Maintainer -
-------------------------------------------------------------------------------

CacheRouter is maintained by Steve Rude.

Drupal.org: http://drupal.org/user/73183
Blog: http://slantview.com/
Twitter: http://twitter.com/slantview
Email: steve [at] slantview.com


-------------------------------------------------------------------------------
- Thanks -
-------------------------------------------------------------------------------

To all the people who have helped out with this module, thank you! Here is a
very partial list of people who have been a big help or inspiration.

Robert Douglass (rdouglass), Bill O'Connor (csevb10), Chris Fuller (cfuller12),
Josh Koenig (joshk),Greg Harvey (greg.harvey), Jonah Ellison, Andrey Postnikov 
(andypost), Raymond Muilwijk (R.Muilwijk), John Vandyk, Károly Négyesi

And to everyone else who's tested, bitched, ripped off (ahem, i mean forked),
used, told me this was a shit idea, contributed, opened issues and listened 
to me talk endlessly about cache router, thank you. srsly.

