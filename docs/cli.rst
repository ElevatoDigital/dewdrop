.. module:: dewdrop
.. module:: dewdrop.cli

CLI
===

The Dewdrop CLI tools help to streamline and standardize common development
tasks.  The CLI runner will be installed automatically along with the
Dewdrop library.  You can find it in the root folder of your WordPress
installation (the folder that contains your wp-config.php by default).

To run Dewdrop CLI, just change to that folder and run the following command::

    ./dewdrop

Because we've not specified a command, ``dewdrop`` will list all the
available commands along with a brief description of that command's purpose.

This section will cover each command and then provide guidance on how you
can extend or write additional commands.

.. toctree::
    :maxdepth: 2

    cli-dbdeploy
