<?php


namespace Application\Factory;


use Application\Repository\Message;
use Application\Service\MessageTableGateway;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Entity\Message as MessageEntity;

/**
 * Class RepositoryMessageFactory
 *
 * @package Application\Factory
 */
class RepositoryMessageFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface  $container
     * @param  string  $requestedName
     * @param  array|null  $options
     *
     * @return Message|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Message($container->get(MessageTableGateway::class), new MessageEntity());
    }
}