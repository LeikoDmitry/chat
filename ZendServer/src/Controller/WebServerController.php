<?php


namespace Application\Controller;

use Application\Repository\Message as RepositoryMessage;
use Application\Service\WebSocketServer;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Exception;

/**
 * Class WebServerController
 *
 * @package Application\Controller
 */
class WebServerController extends AbstractConsoleController
{
    /**
     * @var RepositoryMessage
     */
    private $repository;

    /**
     * @var WebSocketServer $socketServer
     */
    private $socketServer;

    /**
     * WebServerController constructor.
     *
     * @param  RepositoryMessage  $message
     * @param  WebSocketServer  $socketServer
     */
    public function __construct(RepositoryMessage $message, WebSocketServer $socketServer)
    {
        $this->repository = $message;
        $this->socketServer = $socketServer;
    }

    /**
     * @return array|void
     * @throws Exception
     */
    public function indexAction()
    {
        $this->console->write("Start Server...\n");
        $this->console->write("Quit the server with CONTROL-C.\n");
        $this->socketServer->acceptMessages($this->repository);
        $this->socketServer->closeServer();
    }
}