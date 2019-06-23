<?php


namespace Application\Factory;


use Application\Controller\WebServerController;
use Application\Repository\Message as RepositoryMessage;
use Application\Service\WebSocketServer;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class WebServerControllerFactory
 *
 * @package Application\Factory
 */
class WebServerControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface  $container
     * @param  string  $requestedName
     * @param  array|null  $options
     *
     * @return WebServerController|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new WebServerController($container->get(RepositoryMessage::class), $container->get(WebSocketServer::class));
    }
}