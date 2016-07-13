<?php

namespace Dewdrop\Silex\Service;

use ReflectionFunction;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This class provides the ability to declare Silex controller dependencies by class name, where the dependencies are
 * automatically injected into the controller.
 *
 * It gives smart IDE's the ability to provide autocomplete support for such dependencies within controller functions,
 * without requiring the developer to provide type hint docblocks or to reference such services by array syntax with
 * hard-coded keys.
 *
 * By default, classes are instantiated with no constructor arguments and require no configuration.
 *
 * If a service needs a factory to instantiate it, provide the service class name as the key, and the Silex application
 * key where the factory function exists as the value. For example, consider the following:
 *
 * <pre>
 * // Register the service
 * $silex->register(new ControllerInjector([
 *     'My\Service' => 'my-service',
 * ]));
 *
 * // Make a route for a simple dependency that can be instantiated with no constructor arguments
 * $silex->get('/run-something', function (Something $something) {
 *     return $something->report();
 * });
 *
 * // Define a shared instance of My\Service, which requires some configuration during instantiation
 * $silex['my-service'] = $silex->share(function () {
 *     return new \My\Service(new SomeDependency());
 * });
 *
 * // Make a route that declares My\Service as a dependency
 * $silex->get('/run-my-service', function (\My\Service $myService) {
 *     return $myService->run();
 * });
 * </pre>
 *
 * When the browser is directed to /run-something, this service automatically instantiates a new Something object and
 * makes it available to the controller closure, so that the result of its report() method is returned.
 *
 * When the browser is directed to /run-my-service, this service automatically fetches the My\Service object from the
 * application container and passes it to the controller closure, so that the result of its run() method is returned.
 */
class ControllerInjector implements ServiceProviderInterface
{
    /**
     * Map of service classes to factory functions.
     *
     * @var array
     */
    protected $serviceFactoryMap;

    /**
     * Sets the map of services classes to factory functions.
     *
     * @param array $serviceFactoryMap
     */
    public function __construct($serviceFactoryMap = [])
    {
        $this->serviceFactoryMap = $serviceFactoryMap;
    }

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app->on(KernelEvents::CONTROLLER, function (FilterControllerEvent $event) use ($app) {
            $reflectionFunction = new ReflectionFunction($event->getController());
            $parameters         = $reflectionFunction->getParameters();
            foreach ($parameters as $parameter) {
                $class = $parameter->getClass();
                if (!$class) {
                    continue;
                }
                if (array_key_exists($class->name, $this->serviceFactoryMap)) {
                    $service = $app[$this->serviceFactoryMap[$class->name]];
                } else {
                    $service = new $class->name;
                }
                $event->getRequest()->attributes->set($parameter->name, $service);
            }
        });
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     *
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}