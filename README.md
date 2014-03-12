Dewdrop
=======

Dewdrop makes writing complex WordPress plugins simpler by providing a 
sensible project layout and developer tools.


Quick Start
-----------

Dewdrop can be used in two contexts: stand-alone PHP applications and WordPress
plugins.  Depending upon which context you're working in, getting started varies
slightly.

### WordPress Plugins

*Step 1.* Create a folder for your own WordPress plugin inside the plugins folder of your installation.

*Step 2.* Create a composer.json file that requires the "deltasystems/dewdrop" library.  

```json
{
    "require": {
        "deltasystems/dewdrop": "0.9.*"
    }
}
```

**NOTE:** If you will be contributing to Dewdrop, add the following structure to the JSON above so that Composer uses a
Git clone for the Dewdrop dependency. Keep your work committed in branches other than develop and master to avoid losing
work on Composer updates!

```json
{
    "repositories": [
        {
            "type": "git",
            "url":  "git@github.com:DeltaSystems/dewdrop.git"
        }
    ]
}
```

*Step 3.* If you don't have Composer available, you will want to download it as described on Packagist (<http://packagist.org/>).  Once installed run `php composer.phar install --prefer-dist` to install Dewdrop and its dependencies.

*Step 4.* After Composer has installed Dewdrop, you'll want to run a few commands to kick things off.

*Step 5.* Proceed with your plugin development as described in the
[WordPress Codex](https://codex.wordpress.org/Writing_a_Plugin).

```bash
$ ./vendor/bin/dewdrop wp-init       # Create common folders for WordPress plugins
$ ./vendor/bin/dewdrop dbdeploy      # Create stock database tables used by Dewdrop
$ ./vendor/bin/dewdrop dewdrop-test  # Run the Dewdrop test suite to ensure everything is working as expected
```

### Standalone Applications

Quick start instructions for standalone will be posted soon.

Contributing
------------

If you'd like to contribute to Dewdrop, read this wiki page for information on
how to get your development environment running smoothly:

<https://github.com/DeltaSystems/dewdrop/wiki/Contributing>

### Current contributors

* Brad Griffith, Delta Systems
* Darby Felton, Delta Systems


API docs and test reports
-------------------------

You can view our latest build results, including API documentation and test
reports, at:

<http://ci.deltasys.com/dewdrop/>

_Dewdrop is written by Delta Systems and distributed under the GPL license._
