<?php

namespace Application\Controller;

use Application\Repository\Message as RepositoryMessage;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Class IndexController
 *
 * @package Application\Controller
 */
class IndexController extends AbstractActionController
{
    /**
     * @var RepositoryMessage
     */
    private $repository;

    /**
     * IndexController constructor.
     *
     * @param  RepositoryMessage  $repository
     */
    public function __construct(RepositoryMessage $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $messages = $this->repository->fetchAll();
        return new ViewModel(compact('messages'));
    }

    /**
     * @return JsonModel
     */
    public function checkAction()
    {
        if ($this->getRequest()->isGet()) {
            $this->getResponse()->setStatusCode(405);
            return new JsonModel([
               'detail' => 'Method Not Allowed'
            ]);
        }
        $params = $this->getRequest()->getPost();
        $status = '';
        if (isset($params['code']) && mb_strtolower($params['code']) === 'check') {
            $status = $this->repository->clear();
        }
        return new JsonModel([
            'detail' => $status,
        ]);
    }
}
