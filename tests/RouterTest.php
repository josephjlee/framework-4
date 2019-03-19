<?php

namespace Sid\Framework\Test\Unit;

use Codeception\TestCase\Test;

use Doctrine\Common\Annotations\AnnotationReader;

use Symfony\Component\DependencyInjection\Container;

use Sid\ContainerResolver\Resolver\Psr11 as Resolver;

use Sid\Framework\Router;
use Sid\Framework\Router\Exception\RouteNotFoundException;
use Sid\Framework\Router\Route;
use Sid\Framework\Router\RouteCollection;

use Symfony\Component\HttpFoundation\Request;

class RouterTest extends Test
{
    public function testGetRouteCollection()
    {
        $container = new Container();

        $resolver = new Resolver($container);



        $annotations = new AnnotationReader();

        $routeCollection = new RouteCollection($annotations);



        $router = new Router($resolver, $routeCollection);

        $this->assertEquals(
            $routeCollection,
            $router->getRouteCollection()
        );
    }

    public function testConverters()
    {
        $container = new Container();

        $resolver = new Resolver($container);



        $annotations = new AnnotationReader();

        $routeCollection = new RouteCollection($annotations);



        $routeCollection->addController(
            \Controller\ConverterController::class
        );



        $router = new Router($resolver, $routeCollection);

        $match = $router->handle("/converter/double/123", "GET");

        $this->assertEquals(
            246,
            $match->getParams()->get("i")
        );
    }

    /**
     * @dataProvider middlewaresProvider
     */
    public function testMiddlewares($url, $shouldPass)
    {
        $container = new Container();

        $resolver = new Resolver($container);



        $annotations = new AnnotationReader();

        $routeCollection = new RouteCollection($annotations);



        $routeCollection->addController(
            \Controller\MiddlewareController::class
        );



        $router = new Router($resolver, $routeCollection);



        try {
            $match = $router->handle($url, "GET");

            $this->assertTrue($shouldPass);
        } catch (RouteNotFoundException $e) {
            $this->assertFalse($shouldPass);
        }
    }

    public function middlewaresProvider()
    {
        return [
            [
                "url"        => "/middleware/true",
                "shouldPass" => true,
            ],

            [
                "url"        => "/middleware/false",
                "shouldPass" => false,
            ],

            [
                "url"        => "/middleware/true-false",
                "shouldPass" => false,
            ],

            [
                "url"        => "/middleware/false-true",
                "shouldPass" => false,
            ],
        ];
    }

    /**
     * @dataProvider requirementsProvider
     */
    public function testRequirements($url, $shouldPass)
    {
        $container = new Container();

        $resolver = new Resolver($container);



        $annotations = new AnnotationReader();

        $routeCollection = new RouteCollection($annotations);



        $routeCollection->addController(
            \Controller\RequirementsController::class
        );



        $router = new Router($resolver, $routeCollection);



        try {
            $match = $router->handle($url, "GET");

            $this->assertTrue($shouldPass);
        } catch (RouteNotFoundException $e) {
            $this->assertFalse($shouldPass);
        }
    }

    public function requirementsProvider()
    {
        return [
            [
                "url"        => "/requirements/123",
                "shouldPass" => true,
            ],

            [
                "url"        => "/requirements/hello",
                "shouldPass" => false,
            ],

            [
                "url"        => "/requirements/123.456",
                "shouldPass" => false,
            ],
        ];
    }

    public function testRouteNotFoundException()
    {
        $this->expectException(
            RouteNotFoundException::class
        );



        $container = new Container();

        $resolver = new Resolver($container);



        $annotations = new AnnotationReader();

        $routeCollection = new RouteCollection($annotations);



        $router = new Router($resolver, $routeCollection);

        $router->handle("/this/is/a/route/that/doesnt/exist", "GET");
    }

    public function testHttpMethods()
    {
        $container = new Container();

        $resolver = new Resolver($container);



        $annotations = new AnnotationReader();

        $routeCollection = new RouteCollection($annotations);



        $routeCollection->addController(
            \Controller\HttpMethodController::class
        );



        $router = new Router($resolver, $routeCollection);



        $getMatch = $router->handle("/", "GET");

        $this->assertEquals(
            "get",
            $getMatch->getPath()->getAction()
        );



        $postMatch = $router->handle("/", "POST");

        $this->assertEquals(
            "post",
            $postMatch->getPath()->getAction()
        );
    }

    public function testGetRoutes()
    {
        $container = new Container();

        $resolver = new Resolver($container);



        $annotations = new AnnotationReader();

        $routeCollection = new RouteCollection($annotations);



        $router = new Router($resolver, $routeCollection);


        $this->assertEquals(
            0,
            count($routeCollection->getRoutes())
        );



        $routeCollection->addController(
            \Controller\IndexController::class
        );

        $this->assertEquals(
            1,
            count($routeCollection->getRoutes())
        );



        $routeCollection->addController(
            \Controller\RequirementsController::class
        );

        $this->assertEquals(
            2,
            count($routeCollection->getRoutes())
        );



        $routes = $routeCollection->getRoutes();

        foreach ($routes as $route) {
            $this->assertInstanceOf(
                Route::class,
                $route
            );
        }
    }
}