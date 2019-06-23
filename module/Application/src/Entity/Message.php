<?php

namespace Application\Entity;

use Zend\Hydrator\ReflectionHydrator as ReflectionHydrator;

use DateTime;

/**
 * Class Message
 *
 * @package Application\Entity
 */
class Message
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $text;

    /**
     * @var integer
     */
    private $user_ip;

    /**
     * @var DateTime
     */
    private $created;

    /**
     * @var ReflectionHydrator
     */
    private $hydrator;

    /**
     * @var
     */
    private $model;

    /**
     * Message constructor.
     *
     * @param  array  $store
     */
    public function __construct(array $store = [])
    {
        $this->hydrator = new ReflectionHydrator();
        $object = $this->hydrator->hydrate($store, $this);
        $this->setModel($object);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  mixed  $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param  mixed  $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getUserIp()
    {
        return $this->user_ip;
    }

    /**
     * @param  mixed  $user_ip
     */
    public function setUserIp($user_ip)
    {
        $this->user_ip = $user_ip;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param  mixed  $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param  mixed  $model
     */
    public function setModel($model): void
    {
        $this->model = $model;
    }
}