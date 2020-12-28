# Lurkerlite

Lurkerlite provides PHP utilities for monitoring files and directories for changes.  It is a fork of
[henrikbjorn/lurker](https://github.com/flint/Lurker/).

[![Build Status](https://travis-ci.com/totten/Lurker.svg?branch=master)](https://travis-ci.com/totten/Lurker)

## Usage

Use `composer` to install the package:

``` bash
composer require totten/lurkerlite
```

Lurker works by giving the resource watcher a tracking id which is the name of the event and a path to
the resource you want to track.

When all the resources have been added that should be track you would want to add event listeners for them so
your can act when the resources are changed.

``` php
<?php

use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;

$watcher = new ResourceWatcher;
$watcher->track('twig.templates', '/path/to/views');

$watcher->addListener('twig.templates', function (FilesystemEvent $event) {
    echo $event->getResource() . 'was' . $event->getTypeString();
});

$watcher->start();
```

The above example would watch for all events `create`, `delete` and `modify`. This can be controlled by passing a 
third parameter to `track()`.

``` php
<?php

$watcher->track('twig.templates', '/path/to/views', FilesystemEvent::CREATE);
$watcher->track('twig.templates', '/path/to/views', FilesystemEvent::MODIFY);
$watcher->track('twig.templates', '/path/to/views', FilesystemEvent::DELETE);
$watcher->track('twig.templates', '/path/to/views', FilesystemEvent::ALL);
```

Note that `FilesystemEvent::ALL` is a special case and of course means it will watch for every type of event.

## Comparison

`totten/lurkerlite` v1.3 is a fork of [henrikbjorn/lurker](https://github.com/flint/Lurker/) v1.2.  The original
`lurker` provides a portable `ResourceWatcher` which monitors files and directories.  Depending on operating-system and
runtime support, it chooses a different backend driver for tracking files.  This is a great idea, and the
implementation has lots of tests.

The distinguishing characteristic of `lurkerlite` is that it has no formal dependencies on other packages, which means it is:

* Less prone to version conflicts and stale dependencies
* Safer to embed in more contexts

`lurkerlite` should be a drop-in replacement for `lurker` *unless* you previously customized the event-dispatcher.  If
you customized the event-dispatcher, see [CHANGELOG.md](CHANGELOG.md).

## Known Issues (*Patch-welcome*)

Lurker is designed to support multiple file-watching backends, most notably:

* `RecursiveIteratorTracker`: A portable (but less-efficient) backend based on filesystem polling.
* `InotifyTracker`: A more efficient backend based on the Linux `inotify` API and PECL's `inotify` extension.

At time of writing, `InotifyTracker` does not pass its tests, and the cause has not been determined.  (Did it ever
work?  Does it only work in certain environments?) Consequently, it is disabled by default in the stable release.
(To use the unstable implementation, call `new ResourceWatcher(new InotifyTracker())`.

See also:

* `\Lurker\Tests\Tracker\TrackerTest::testMoveSubdirResource()`
* `\Lurker\Tests\Tracker\InotifyTrackerTest::testMoveSubdirResource()`
* https://github.com/flint/Lurker/issues/32
