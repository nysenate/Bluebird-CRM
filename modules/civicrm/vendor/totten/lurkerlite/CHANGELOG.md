CHANGELOG
=========

lurkerlite 1.3.0 (2020-08-31)
------------------

  `totten/lurkerlite` 1.3 is a fork of `henrikbjorn/lurker` 1.2.  It primarily aims to address dependency-management
  issues which have been outstanding for multiple years -- and also to merge some of the PR backlog.

  This revision should be a drop-in replacement *unless* you customize the event-dispatcher. Compare:

  * __Default dispatch__: By default, `ResourceWatcher` creates its own internal event-dispatcher.
    If you use this default, then the update should be a drop-in replacement.
  * __Custom dispatch__: The `ResourceWatcher` previously accepted an optional argument based on
    `\Symfony\Component\EventDispatcher\EventDispatcherInterface`.  This may have been useful for glueing into another
    dispatcher/application.  This also makes `lurker` a dependency-bottleneck, and it is not supported by `lurkerlite`. 
    If a system attempts to use a Symfony `EventDispatcher`, `lurkerlite` will ignore it and emit a warning
    (`E_USER_DEPRECATED`).

  If you need to bridge `lurker` with another event system, then here is an alternative technique: subscribe to the
  wildcard event (`all`) and pass it to your preferred dispatcher.  This technique should work equally well on
  `lurker` or `lurkerlite`, and it should work with diverse event systems.  Pseudocode:

      ```php
      $w = new \Lurker\ResourceWatcher();
      $w->addListener('all', function(\Lurker\Event\FilesystemEvent $e) use ($myDispatcher) {
        $myEventName = 'resource_watcher.' . $e->getTrackedResource()->getTrackingId();
        $myEvent = new MyFilesystemEvent($e->getResource(), ...);
        $myDispatcher->dispatch($myEventName, $myEvent);
      });
      ```

  More detailed list of changes:

  * Update Travis test matrix to more contemporary PHP builds
  * Remove dependency on `symfony/config` (https://github.com/flint/Lurker/pull/22)
  * Remove dependency on `symfony/event-dispatcher`. (This is an alternative
    to https://github.com/flint/Lurker/pull/29 or https://github.com/flint/Lurker/pull/31.)
  * Use autoload-dev rather than manually adding the tests directory (https://github.com/flint/Lurker/pull/23)
  * Update branch-alias (https://github.com/flint/Lurker/pull/26)
  * Use `RecursiveIteratorTracker` as the default -- effectively disabling `InotifyTracker`
    (unless you specifically opt into it). `InotifyTrackerTest` has been reporting failures (eg https://github.com/flint/Lurker/pull/24 and
    https://github.com/flint/Lurker/issues/32).  The reason is unclear, but it is not any kind of obvious,
    bisectable cause. (Perhaps it was an upstream change in PHP, pecl/inotify, or Linux? Or perhaps it never
    fully worked?) Turn it off until someone figures out how to fix it.
