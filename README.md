Dewdrop
=======

Dewdrop makes writing complex WordPress plugins simpler by providing a 
sensible project layout and developer tools.

Our next steps include adding code generation tools to the command line
interface and fleshing out the model API.  If you're interested in
contributing, contact Brad at <bgriffith@deltasys.com>.


Quick Start
-----------

### Step 1

Download and install the latest release of WordPress:
    
    <http://wordpress.org/latest.zip>

### Step 2

Run the Dewdrop installer script from within the root folder of your WordPress installation:

    php <(curl -sS https://raw.github.com/DeltaSystems/dewdrop/master/installer)

### Done

Once the installer completes, you'll have a new plugin in place with the default
Dewdrop layout.  Additionally, the Dewdrop library and it's dependencies will be
wired in and accessible via the command line tool.

See what's available to you by running:

    ./dewdrop help


Testing and style
-----------------

Unit tests are greatly appreciated.  We aim to have over 90% code coverage and 
high-quality, relevant tests.  We also value integration tests to help us ensure
that Dewdrop-based plugins continue working reliably with new WordPress releases.
Code should be written to conform with PSR-2 style.


_Dewdrop is written by Delta Systems and distributed under the GPL license._
