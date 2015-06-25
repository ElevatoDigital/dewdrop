<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use Dewdrop\Auth\Db\UserPasswordChangeTokensTableGateway;
use Dewdrop\Auth\Db\UserRowGateway;
use Dewdrop\Mail\View\View as MailView;
use Silex\Application;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

/**
 * This class provides authentication and authorization services for Dewdrop applications outside of WordPress.
 *
 * First, this component needs to be made available within the Silex application object. A good place to do this is the
 * application bootstrap:
 *
 * <pre>
 * $application['auth'] = $application->share(
 *     function (\Silex\Application $app) {
 *         $auth = new Auth($app);
 *         $auth->setTitle('My Project');
 *         return $auth;
 *     }
 * );
 * </pre>
 *
 * \Dewdrop\Bootstrap\Detector creates a 'users-gateway' service, an instance of \Dewdrop\Db\Table\UsersTableGateway.
 * This requires database schema similar to the following:
 *
 * <pre>
 * CREATE TABLE security_levels (
 *     security_level_id SERIAL PRIMARY KEY,
 *     name VARCHAR(64) NOT NULL UNIQUE
 * );
 *
 * CREATE TABLE users (
 *     user_id SERIAL PRIMARY KEY,
 *     security_level_id INTEGER NOT NULL REFERENCES security_levels,
 *     username VARCHAR(64) NOT NULL UNIQUE,
 *     first_name VARCHAR(128) NOT NULL,
 *     last_name VARCHAR(128) NOT NULL,
 *     email_address VARCHAR(512) NOT NULL UNIQUE,
 *     password_hash VARCHAR(60) NOT NULL
 * );
 *
 * CREATE TABLE user_password_change_tokens (
 *     user_password_change_token_id SERIAL PRIMARY KEY,
 *     user_id INTEGER NOT NULL REFERENCES users,
 *     token VARCHAR(60) NOT NULL UNIQUE,
 *     date_created TIMESTAMP NOT NULL DEFAULT NOW()
 *     used BOOLEAN NOT NULL DEFAULT FALSE
 * );
 * </pre>
 *
 * Of course additional tables and columns are permissible, but those above are required. You can extend the user table
 * and user row classes, \Dewdrop\Auth\Db\UsersTableGateway and \Dewdrop\Auth\Db\UserRowGateway if necessary.
 *
 * You can easily generate a password hash for development and testing purposes using a Dewdrop CLI command:
 *
 * <pre>
 * $ ./vendor/bin/dewdrop auth-hash-password --plaintext <password>
 * </pre>
 *
 * Next, the component needs to be initialized early in the application lifecycle. Notice how in the following example
 * the Dewdrop user management component is also registered with the admin component:
 *
 * <pre>
 * $zend = realpath(__DIR__ . '/../zend');
 *
 * if (file_exists($zend)) {
 *     define('PROJECT_ROOT', $zend);
 * } else {
 *     define('PROJECT_ROOT', realpath(__DIR__ . '/../'));
 * }
 *
 * require_once PROJECT_ROOT . '/vendor/autoload.php';
 *
 * $silex = \Dewdrop\Pimple::getInstance();
 *
 * $silex['auth']->init();
 *
 * $silex['admin']->registerComponentsInPath();
 *
 * $silex['admin']->registerComponent(new \Dewdrop\Admin\Component\Stock\Users\Component());
 *
 * $silex->run();
 * </pre>
 */
class Auth
{
    /**
     * Silex application
     *
     * @var Application
     */
    protected $app;

    /**
     * Route class map
     *
     * @var array
     */
    protected $routeClassMap = array(
        '/auth/login'           => '\Dewdrop\Auth\Page\Login',
        '/auth/forgot-password' => '\Dewdrop\Auth\Page\ForgotPassword',
        '/auth/reset-password'  => '\Dewdrop\Auth\Page\ResetPassword',
    );

    /**
     * Title.  This will be escaped and used in the title tag.
     *
     * @var string
     */
    protected $title = 'Welcome';

    /**
     * Optionally specify some HTML to use in the header of the panel
     * on each page (e.g. a logo image).  If not specified, then the
     * title text will be used.
     *
     * @var string
     */
    protected $headerHtml;

    /**
     * Layout script path
     *
     * @var string
     */
    protected $layoutScriptPath;

    /**
     * Layout script filename
     *
     * @var string
     */
    protected $layoutScript = 'layout.phtml';

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->layoutScriptPath = __DIR__ . '/Auth/Page/view-scripts';
    }

    /**
     * Allow over-riding of default page classes for auth routes.  Makes it
     * possible to do custom pages, rather than being stuck with the Dewdrop
     * defaults.
     *
     * @param string $route
     * @param string $className
     * @return Auth
     */
    public function assignRouteClass($route, $className)
    {
        $this->routeClassMap[$route] = $className;

        return $this;
    }

    /**
     * Generate a password change token and send the given user an email with a link to reset password
     *
     * @param UserRowGateway $user
     * @return Auth
     */
    public function forgotPassword(UserRowGateway $user)
    {
        $token = password_hash(openssl_random_pseudo_bytes(64), PASSWORD_BCRYPT);

        $tokenTableDataGateway = new UserPasswordChangeTokensTableGateway();

        $tokenTableDataGateway->insert([
            'user_id' => $user->getId(),
            'token'   => $token,
        ]);

        if ('80' !== $_SERVER['SERVER_PORT']) {
            $port = ":{$_SERVER['SERVER_PORT']}";
        } else {
            $port = '';
        }

        $resetPasswordUrl = "http://{$_SERVER['SERVER_NAME']}{$port}/auth/reset-password?token=" .
            rawurlencode($token);

        $mailView = new MailView();
        $mailView
            ->assign('resetPasswordUrl', $resetPasswordUrl)
            ->assign('title', $this->title)
            ->setScriptPath(__DIR__ . '/Auth/view-scripts');

        $bodyHtml = $mailView->render('forgot-password-email-html.phtml');
        $bodyText = $mailView->render('forgot-password-email-text.phtml');

        $message = Swift_Message::newInstance();
        $message
            ->setSubject('Reset Password')
            ->setFrom("noreply@{$_SERVER['SERVER_NAME']}")
            ->setTo($user->getEmailAddress());
        $message->setBody($bodyHtml, 'text/html');
        $message->addPart($bodyText, 'text/plain');

        $this->app['mailer']->send($message);

        return $this;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Auth
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * HTML to use on the header of the panel on each page.
     *
     * @param $headerHtml
     * @return $this
     */
    public function setHeaderHtml($headerHtml)
    {
        $this->headerHtml = $headerHtml;

        return $this;
    }

    /**
     * Get the HTML that should be used in the header of the panel on each page.
     *
     * @return string
     */
    public function getHeaderHtml()
    {
        return $this->headerHtml;
    }

    /**
     * Set layout script path
     *
     * @param string $layoutScriptPath
     * @return Auth
     */
    public function setLayoutScriptPath($layoutScriptPath)
    {
        $this->layoutScriptPath = $layoutScriptPath;

        return $this;
    }

    /**
     * Get layout script path
     *
     * @return string
     */
    public function getLayoutScriptPath()
    {
        return $this->layoutScriptPath;
    }

    /**
     * Set layout script
     *
     * @param string $layoutScript
     * @return Auth
     */
    public function setLayoutScript($layoutScript)
    {
        $this->layoutScript = $layoutScript;

        return $this;
    }

    /**
     * Get layout script
     *
     * @return string
     */
    public function getLayoutScript()
    {
        return $this->layoutScript;
    }

    /**
     * Get user
     *
     * @return UserRowGateway|null
     */
    public function getUser()
    {
        $user  = null;
        $token = $this->app['security']->getToken();

        if (null !== $token) {
            $user = $token->getUser();
        }

        return $user;
    }

    /**
     * Returns whether user is available
     *
     * @return bool
     */
    public function hasUser()
    {
        return null !== $this->getUser();
    }

    /**
     * Initializations
     *
     * @return Auth
     */
    public function init()
    {
        $app = $this->app;

        $app->register(new SecurityServiceProvider());
        $app->register(new RememberMeServiceProvider());

        $app->register(new SwiftmailerServiceProvider());

        $app['security.firewalls'] = $this->getSecurityFirewallsConfig();

        $app['security.encoder.digest'] = $app->share(
            function () {
                return new BCryptPasswordEncoder(6);
            }
        );

        $app['user'] = $app->share(
            function () {
                $token = $this->app['security']->getToken();

                if (null !== $token) {
                    return $token->getUser();
                } else {
                    return null;
                }
            }
        );

        foreach ($this->routeClassMap as $route => $pageClassName) {
            $app->match(
                $route,
                function (Request $request) use ($app, $pageClassName) {
                    $page = new $pageClassName($this, $app, $request);
                    return $page->respond();
                }
            );
        }

        return $this;
    }

    /**
     * Get security firewalls configuration
     *
     * @return array
     */
    protected function getSecurityFirewallsConfig()
    {
        return [
            '^/auth/reset-password' => [
                'pattern'   => '^/auth/reset-password',
                'anonymous' => true,
            ],
            '^/admin/'           => [
                'pattern'     => '^/admin/',
                'form'        => [
                    'login_path' => '/auth/login',
                    'check_path' => '/admin/login-check',
                ],
                'logout'      => [
                    'logout_path' => '/admin/logout',
                ],
                'remember_me' => [
                    'key' => 'yj/5Hf#K#^{G.T*T>g0I+iXKFyy{%KM:DkRH~X6>dV"s|$1UhDEM(Uy5?-Pbp',
                ],
                'users'       => $this->app->share(function () {
                    return $this->app['users-gateway'];
                }),
            ],
        ];
    }
}
