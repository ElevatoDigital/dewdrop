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
    "autoload": {
        "psr-4": {
            "Model\\": "models/"
        }
    },
    "require": {
        "deltasystems/dewdrop": "dev-master"
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

```bash
$ ./vendor/bin/dewdrop wp-init       # Create common folders for WordPress plugins
$ ./vendor/bin/dewdrop dbdeploy      # Create stock database tables used by Dewdrop
$ ./vendor/bin/dewdrop dewdrop-test  # Run the Dewdrop test suite to ensure everything is working as expected
```

*Step 5.* Dewdrop requires the installation of the [WordPress Session Manager Plugin](https://wordpress.org/plugins/wp-session-manager/). You will need to install it.

*Step 6.* Proceed with your plugin development as described in the
[WordPress Codex](https://codex.wordpress.org/Writing_a_Plugin).

### Standalone Applications

Dewdrop may also be used as a standalone application, outside the context of a WordPress instance. To do so requires an
additional dependency on [Silex](http://silex.sensiolabs.org/), a PHP micro-framework.

1. Add a `.gitignore` file in your project root directory that includes the `/vendor` directory, since we'll use
    [Composer](https://getcomposer.org/) to install Dewdrop and its dependencies. 
1. Add a `composer.json` file similar to the example below:

    ```json
    {
        "autoload": {
            "psr-4": {
                "My\\": "src/My/"
            }
        },
        "require": {
            "deltasystems/dewdrop": "dev-master",
            "silex/silex": "~1.2"
        },
        "repositories": [
            {
                "type": "git",
                "url": "git@github.com:DeltaSystems/dewdrop.git"
            }
        ]
    }
    ```
1. Run `composer install` to install Dewdrop and its dependencies.
1. Add a `dewdrop-config.php` file similar to the example below:
    ```php
    <?php
    
    // Change the configuration for your MySQL or PostgreSQL database as appropriate
    return [
        'bootstrap' => '\My\Bootstrap',
        'db'        => [
            'username' => 'dewdrop_pgsql',
            'password' => 'dewdrop_pgsql',
            'host'     => 'localhost',
            'name'     => 'dewdrop_pgsql',
            'type'     => 'pgsql',
        ],
    ];    
    ```
1. Add an application bootstrap class. In this example your bootstrap class would be located at `src/My/Bootstrap.php`
    and may resemble something like the following:
    ```php
    <?php
    
    namespace My;
    
    use Dewdrop\Bootstrap\PimpleProviderInterface;
    use Dewdrop\Config;
    use Dewdrop\Db\Adapter as DbAdapter;
    use Dewdrop\Db\Driver\Pdo\Pgsql;
    use Exception;
    use PDO;
    use Silex\Application;
    
    class Bootstrap implements PimpleProviderInterface
    {
        /**
         * @var Application
         */
        protected $application;
    
        /**
         * @return Application
         */
        public function getPimple()
        {
            if (null === $this->application) {
    
                $this->application = new Application();
    
                $this->application['config'] = $this->application->share(
                    function () {
                        return new Config();
                    }
                );
    
                $this->application['db'] = $this->application->share(
                    function () {
    
                        if (!isset($this->application['config']['db'])) {
                            throw new Exception('Database configuration unavailable');
                        }
    
                        $dbConfig = $this->application['config']['db'];
    
                        $pdo = new PDO(
                            "pgsql:dbname={$dbConfig['name']};host={$dbConfig['host']}",
                            $dbConfig['username'],
                            $dbConfig['password']
                        );
    
                        $adapter = new DbAdapter();
    
                        new Pgsql($adapter, $pdo);
    
                        return $adapter;
                    }
                );

                $this->application['admin'] = $this->application->share(
                    function () {
                        $admin = new SilexAdmin($this->application);
    
                        $admin->setTitle('My Application');
    
                        return $admin;
                    }
                );

            }
    
            return $this->application;
        }
    }
    ```
1. Add a `models` directory to your project root.
1. Run `vendor/bin/dewdrop dbdeploy` to generate your project's database metadata files and supporting database schema.
1. Optionally run `vendor/bin/dewdrop dewdrop-test` to run the tests for Dewdrop itself.
1. Add a `www` directory to your project root and an `index.php` file similar to the following example:
    ```php
    <?php
    
    define('PROJECT_ROOT', realpath(__DIR__ . '/../'));
    
    require_once PROJECT_ROOT . '/vendor/autoload.php';
    
    /* @var $silex \Silex\Application */
    $silex = \Dewdrop\Pimple::getInstance();
    
    $silex->get('/', function () use ($silex) {
        return 'Hello, world!';
    });
    
    $silex->run();
    ```
1. Serve the `www` directory with your favorite web server.
1. Check the operation of your web application with a GET request to the application root. You should see a response of
    "Hello, world!".
1. Explore more about Dewdrop, such as building administrative user interfaces with CRUD support.




Contributing
------------

If you'd like to contribute to Dewdrop, read this wiki page for information on
how to get your development environment running smoothly:

<https://github.com/DeltaSystems/dewdrop/wiki/Contributing>

### [Current contributors](https://github.com/DeltaSystems/dewdrop/graphs/contributors)


API docs and test reports
-------------------------

You can view our latest build results, including API documentation and test
reports, at:

<http://ci.deltasys.com/dewdrop/>

_Dewdrop is written by Delta Systems and distributed under the GPL license._
