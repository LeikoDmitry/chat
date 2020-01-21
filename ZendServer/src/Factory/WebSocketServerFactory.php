<?php

namespace Application\Factory;

use Application\Service\WebSocketServer;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Exception\ExceptionWebSocketServer;

/**
 * Class WebSocketServerFactory
 *
 * @package Application\Factory
 */
class WebSocketServerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface  $container
     * @param  string  $requestedName
     * @param  array|null  $options
     *
     * @return WebSocketServer|object
     * @throws ExceptionWebSocketServer
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new WebSocketServer($container->get('config') ?? []);
    }
}