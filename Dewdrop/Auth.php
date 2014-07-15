<?php

namespace Dewdrop;

use Dewdrop\Auth\Db\UserRowGateway;
use Silex\Application;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

/**
 * This class provides authentication and authorization services for Dewdrop applications outside of WordPress.
 */
class Auth
{
    /**
     * @var Application
     */
    protected $app;

    protected $routeClassMap = array(
        '/auth/login'           => '\Dewdrop\Auth\Page\Login',
        '/auth/forgot-password' => '\Dewdrop\Auth\Page\ForgotPassword'
    );

    protected $title = 'Welcome';

    protected $layoutScriptPath;

    protected $layoutScript = 'layout.phtml';

    /**
     * @param Application $app
     * @return void
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

    public function setTitle($title)
    {
        $this->title = $title;

        return $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setLayoutScriptPath($layoutScriptPath)
    {
        $this->layoutScriptPath = $layoutScriptPath;

        return $this;
    }

    public function getLayoutScriptPath()
    {
        return $this->layoutScriptPath;
    }

    public function setLayoutScript($layoutScript)
    {
        $this->layoutScript = $layoutScript;

        return $this;
    }

    public function getLayoutScript()
    {
        return $this->layoutScript;
    }

    /**
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
     * @return bool
     */
    public function hasUser()
    {
        return null !== $this->getUser();
    }

    /**
     * @return Auth
     */
    public function init()
    {
        $app = $this->app;

        $app->register(new SecurityServiceProvider());
        $app->register(new RememberMeServiceProvider());

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
     * @return array
     */
    protected function getSecurityFirewallsConfig()
    {
        return [
            'admin' => [
                'pattern' => '^/admin/',
                'form'    => [
                    'login_path' => '/auth/login',
                    'check_path' => '/admin/login-check',
                ],
                'logout'  => [
                    'logout_path' => '/admin/logout',
                ],
                'remember_me' => [
                    'key' => 'yj/5Hf#K#^{G.T*T>g0I+iXKFyy{%KM:DkRH~X6>dV"s|$1UhDEM(Uy5?-Pbp',
                ],
                'users' => $this->app->share(function () {
                    return $this->app['users-gateway'];
                }),
            ],
        ];
    }
}
