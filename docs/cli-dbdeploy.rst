.. module:: dewdrop
.. module:: dewdrop.cli

dbdeploy
========

The dbdeploy command uses a log to track and manage changes to your database
schema and fixtures.  When running the dbdeploy command, it checks the
``dbdeploy_changelog`` table against the available SQL revision files in
your plugin, and applies those scripts that aren't already present in the
change log.

dbdeploy can be very useful when you're coordinating changes to your database
with other developers or even just as a single developer running your plugin
in multiple environments (e.g. developing in one environment, testing in a 
staging environment, and then ultimately deploying to production).

The dbdeploy command will look for SQL change files in your plugin's ``db/``
folder.  If expects the file names to be of the form 
"xxxxx-brief-description-of-change.sql" where "xxxxx" is the revision
number padded with zeros.  For example, your change file might be named
"00003-add-table-for-whozeewhatzits.sql".


Basic Usage
-----------

Apply all available revisions::

    ./dewdrop dbdeploy

Check to see what revision is currently applied and which are available::

    ./dewdrop dbdeploy status


Creating New Tables
-------------------

A couple notes about creating new database tables.  First, we highly
recommend that you create your tables using the InnoDB storage engine
and the UTF-8 character set::

    CREATE TABLE whozeewhatzits (
        whozeewhatzit_id INTEGER PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(128) NOT NULL,
        foo_id INTEGER NOT NULL,
        INDEX (name),
        INDEX (foo_id),
        FOREIGN KEY (foo_id) REFERENCES foos (foo_id)
    ) ENGINE=InnoDB CHARSET=utf8;

Second, Dewdrop also provides the ``gen-db-table`` command, which will
create a new dbdeploy SQL file and a model class for your new table.
After running ``gen-db-table``, you'll can edit the SQL file to include
the columns and indexes you need.


dbdeploy Automatically Updates DB Metadata
------------------------------------------

Once it is done applying available revisions, dbdeploy will execute the
``db-metadata`` command automatically, updating the metadata definition
files used by Dewdrop DB.


Back-filling the Log
--------------------

If for some reason your changelog has become out of sync with the actual
state of your database schema, you can back-fill the log so that it
will reflect the fact that some revisions have alrady been applied.  This
can be useful if, for example, someone has manually applied a schema
revision rather than using dbdeploy.  To back-fill the changelog up to
revision number 5, run::

    ./dewdrop dbdeploy backfill --revision=5 --changeset=plugin


dbdeploy Manages Multiple Change Sets
-------------------------------------

The ``db/`` folder of your plugin is actually only one of 3 change sets
managed by Dewdrop's dbdeploy command.  Whenever you run dbdeploy, it will
also check for revisions to the DB tables need by Dewdrop's core features
and Dewdrop's unit tests.  You generally should not need to worry about
these changes.  You'll notice these separate change sets when checking
dbdeploy's status or updating Dewdrop, but otherwise, you will not need
to interact with them unless you are working on Dewdrop's core functionality.

