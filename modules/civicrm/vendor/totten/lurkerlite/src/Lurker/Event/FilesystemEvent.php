<?php

namespace Lurker\Event;

/**
 * Resource change event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FilesystemEvent
{
    const CREATE = 1;
    const MODIFY = 2;
    const DELETE = 4;
    const ALL    = 7;

    use FilesystemEventTrait;
}
