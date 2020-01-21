<?php


namespace Application\Service;


use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class MessageTableGateway
 *
 * @package Application\Service
 */
class MessageTableGateway implements FactoryInterface
{
    /**
     * @var string
     */
    const TABLE_NAME = 'message';

    /**
     * @param  ContainerInterface  $container
     * @param  string  $requestedName
     * @param  array|null  $options
     *
     * @return object|TableGateway
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TableGateway(self::TABLE_NAME, $container->get(AdapterInterface::class));
    }
}