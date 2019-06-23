<?php


namespace Application\Factory;


use Application\Controller\IndexController;
use Application\Repository\Message as RepositoryMessage;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface  $container
     * @param  string  $requestedName
     * @param  array|null  $options
     *
     * @return IndexController|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new IndexController($container->get(RepositoryMessage::class));
    }
}