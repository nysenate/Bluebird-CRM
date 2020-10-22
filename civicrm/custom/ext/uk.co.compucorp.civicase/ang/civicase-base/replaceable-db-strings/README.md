## Replaceable DB Strings

Here we include a list of strings stored in the database and could potentially be translated by other extensions.

We wrap them in a comment and in a `ts` function because the CiviCRM translation service picks the string up inside a `ts` function, even if they are in a comment. Also, comments are not included in the minified javascript file output.
