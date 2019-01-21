# Doctor When: Development

## Development: Add a new task

`DoctorWhen` supports timestamp migration for activities and cases. However,
it's anticipated that several other changes will be appropriate (such as
converting `DATETIME` columns to `TIMESTAMP`).

To add a new task:
 * Copy `CRM/DoctorWhen/Cleanups/Example.php` to a new file (eg `CRM/DoctorWhen/Cleanups/MyTask.php`).
 * Edit the new file. Update the class name and the title. Consider the examples in the  `enqueue()` function; then rewrite them to do something more useful.
 * Edit `CRM/DoctorWhen/Cleanups.php`. In the `__construct()` function, add a record for the new class.
