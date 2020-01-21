<?php

namespace Application;

use Zend\Router\Http\Literal;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'message' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/check',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'check',
                    ],
                ],
            ],
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'list-users' => [
                    'options' => [
                        'route'    => 'runserver',
                        'defaults' => [
                            'controller' => Controller\WebServerController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            Repository\Message::class => Factory\RepositoryMessageFactory::class,
            Service\MessageTableGateway::class => Service\MessageTableGateway::class,
            Service\WebSocketServer::class => Factory\WebSocketServerFactory::class
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Factory\IndexControllerFactory::class,
            Controller\WebServerController::class => Factory\WebServerControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ]
];
