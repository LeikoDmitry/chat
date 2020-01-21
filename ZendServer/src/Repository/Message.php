<?php


namespace Application\Repository;

use RuntimeException;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Adapter\AdapterInterface;
use Application\Entity\Message as EntityMessage;

/**
 * Class Message
 *
 * @package Application\Repository
 */
class Message
{
    /**
     * @var AdapterInterface
     */
    private $tableGateway;

    /**
     * @var EntityMessage
     */
    private $entity;

    /**
     * @var int
     */
    const STATUS_ACTIVE = 1;

    /**
     * @var int
     */
    const STATUS_DELETE = 2;

    /**
     * @var int
     */
    const LIMIT_MESSAGES = 10;

    /**
     * Message constructor.
     *
     * @param  TableGatewayInterface  $tableGateway
     * @param  EntityMessage  $entity
     */
    public function __construct(TableGatewayInterface $tableGateway, EntityMessage $entity)
    {
        $this->tableGateway = $tableGateway;
        $this->entity = $entity;
    }

    /**
     * @param  int  $offset
     *
     * @return array
     */
    public function fetchAll($offset = 0)
    {
        $resultSet =  $this->tableGateway->select(function (Select $select) use ($offset) {
            $select->columns(['id', 'text', 'created'])
                   ->where(['status' => self::STATUS_ACTIVE])
                   ->order('id DESC')
                   ->offset($offset)
                   ->limit(self::LIMIT_MESSAGES);
        });
        if (method_exists($resultSet, 'toArray')) {
            return $resultSet->toArray();
        }
        return [];
    }

    /**
     * @param  int  $id
     *
     * @return object
     */
    public function getById(int $id)
    {
        if (! $id){
            throw new RuntimeException('ID Must Be Integer');
        }
        $rowSet = $this->tableGateway->select(function(Select $select) use ($id){
            $select->columns(['text', 'created'])
            ->where(['id' => $id, 'status' => self::STATUS_ACTIVE])->limit(1);
        });
        $row = $rowSet->current();
        if (! $row) {
            throw new RuntimeException('Row Not Exist');
        }
        return $row;
    }

    /**
     * Save Data
     * @return integer|string
     */
    public function save()
    {
        $data = [
            'text'    => $this->getEntity()->getText(),
            'created' => $this->getEntity()->getCreated(),
            'user_ip' => $this->getEntity()->getUserIp()
        ];
        $this->tableGateway->insert($data);
        $lastInsertId = $this->tableGateway->getLastInsertValue();
        if (! $lastInsertId) {
            return 'No Rows';
        }
        return $lastInsertId;
    }

    /**
     * Clear Messages
     *
     * @return mixed
     */
    public function clear()
    {
        $count = $this->tableGateway->select()->count();
        if ($count > self::LIMIT_MESSAGES) {
            $resultSet = $this->fetchAll(self::LIMIT_MESSAGES);
            foreach ($resultSet as $result) {
                $this->delete($result['id'] ?? '');
            }
        }
        return $count;
    }

    /**
     * @param  int  $id
     *
     * @return mixed
     */
    public function delete($id)
    {
        try {
            return $this->tableGateway->delete(['id' => (int) $id]);
        } catch (RuntimeException $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @return EntityMessage
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param  $entity
     */
    public function setEntity($entity): void
    {
        $this->entity = $entity;
    }
}