Dewdrop
=======

Dewdrop makes writing complex WordPress plugins simpler by providing a 
sensible project layout and developer tools.


Contributing
------------

If you'd like to contribute to Dewdrop, read this wiki page for information on
how to get your development environment running smoothly:

<https://github.com/DeltaSystems/dewdrop/wiki/Contributing>

### Current contributors

* Brad Griffith, Delta Systems
* Darby Felton, Delta Systems


Quick Start
-----------

### Step 1

Download and install the latest release of WordPress:
    
<http://wordpress.org/latest.zip>

### Step 2

Run the Dewdrop installer script in your terminal from within the root folder 
of your WordPress installation (the folder containing your wp-config.php file):

    php <(curl -sS https://raw.github.com/DeltaSystems/dewdrop/master/installer)

### Done

Once the installer completes, you'll have a new plugin in place with the default
Dewdrop layout.  Additionally, the Dewdrop library and it's dependencies will be
wired in and accessible via the command line tool.

See what's available to you by running:

    ./dewdrop help


API docs and test reports
-------------------------

You can view our latest build results at:

<http://ci.deltasys.com/dewdrop/>


Testing and style
-----------------

Unit tests are greatly appreciated.  We aim to have over 90% code coverage and 
high-quality, relevant tests.  We also value integration tests to help us ensure
that Dewdrop-based plugins continue working reliably with new WordPress releases.
Code should be written to conform with PSR-2 style.


_Dewdrop is written by Delta Systems and distributed under the GPL license._
