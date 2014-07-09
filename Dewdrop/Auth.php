<?php

namespace Dewdrop;

use Dewdrop\Auth\UserProvider;
use Dewdrop\Db\Row\User as UserRow;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;
use Zend\View\View;
use Zend\View\Model\ViewModel;

/**
 * This class provides authentication and authorization services for Dewdrop applications outside of WordPress.
 */
class Auth
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return UserRow|null
     */
    public function getUser()
    {
        $user = null;

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
        return null === $this->getUser();
    }

    /**
     * @return Auth
     */
    public function init()
    {
        $app = $this->app;

        $app->register(new SecurityServiceProvider(), $this->getSecurityServiceProviderConfig());

        $view = new View();

        $app->get('/auth/login', function (Request $request) use ($app, $view) {
            $templatePathStack = new TemplatePathStack();
            $templatePathStack->addPath(__DIR__ . '/Auth');
            $phpRenderer = new PhpRenderer();
            $phpRenderer->setResolver($templatePathStack);
            $viewModel = new ViewModel([
                'error'         => $app['security.last_error']($request),
                'last_username' => $app['session']->get('_security.last_username'),
            ]);
            $viewModel->setTemplate('login.phtml');
            return $phpRenderer->render($viewModel);
        });

        return $this;
    }

    /**
     * @return array
     */
    protected function getSecurityServiceProviderConfig()
    {
        $app = $this->app;

        return [
            'security.firewalls' => [
                'admin' => [
                    'pattern' => '^/admin/',
                    'form'    => [
                        'login_path' => '/auth/login',
                        'check_path' => '/admin/login-check',
                    ],
                    'logout'  => [
                        'logout_path' => '/admin/logout',
                    ],
                    'users'   => $app->share(function () use ($app) {
                        return new UserProvider($app['db']);
                    }),
                ],
            ],
        ];
    }
}