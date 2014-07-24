<?php

return array(
    'service_manager' => array(
        'factories' => array(
            "Zend\Session\SessionManager" => "Profondlib\Session\SessionManagerFactory",
            "Zend\Authentication\AuthenticationService" => "Profondlib\Authentication\AuthenticationServiceFactory",
        ),
        'aliases' => array(
        ),
        'invokables' => array(
            //--Mapper:
            "Profond\Mapper\User" => "Profond\Mapper\UserMapper",
            "Profond\Mapper\Machine" => "Profond\Mapper\MachineMapper",
            "Profond\Mapper\Project" => "Profond\Mapper\ProjectMapper",
            "Profond\Mapper\Config" => "Profond\Mapper\ConfigMapper",
            "Profond\Mapper\Job" => "Profond\Mapper\JobMapper",
            "Profond\Mapper\Executable" => "Profond\Mapper\ExecutableMapper",
            "Profond\Mapper\Asynchtask" => "Profond\Mapper\AsynchtaskMapper",
            //--Service:
            "Profond\Service\User" => "Profond\Service\UserService",
            "Profond\Service\Machine" => "Profond\Service\MachineService",
            "Profond\Service\Project" => "Profond\Service\ProjectService",
            "Profond\Service\Job" => "Profond\Service\JobService",
            "Profond\Service\Config" => "Profond\Service\ConfigService",
            "Profond\Service\Executable" => "Profond\Service\ExecutableService",
            "Profond\Service\Asynchtask" => "Profond\Service\AsynchtaskService",
            //--Form:
            "Profond\Form\AuthentificationForm" => new Profond\Form\AuthentificationForm(),
        ),
        'services' => array(
        // Keys are the service names
        // Values are objects
        //'Auth' => new SomeModule\Authentication\AuthenticationService(),
        ),
    ),
    'router' => array(
        'routes' => array(
            'root' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Profond\Controller\Auth',
                        'action' => 'index',
                    ),
                ),
            ),
            'login' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        'controller' => 'Profond\Controller\Auth',
                        'action' => 'login',
                    ),
                ),
            ),
            'logout' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/logout',
                    'defaults' => array(
                        'controller' => 'Profond\Controller\Auth',
                        'action' => 'logout',
                    ),
                ),
            ),
            'asynch' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/asynch',
                    'defaults' => array(
                        'controller' => 'Profond\Controller\Api',
                        'action' => 'asynch',
                    ),
                ),
            ),
            'isready' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/isready',
                    'defaults' => array(
                        'controller' => 'Profond\Controller\Api',
                        'action' => 'isready',
                    ),
                ),
            ),
            'endjob' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/endjob',
                    'defaults' => array(
                        'controller' => 'Profond\Controller\Api',
                        'action' => 'endjob',
                    ),
                ),
            ),
            'startjob' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/startjob',
                    'defaults' => array(
                        'controller' => 'Profond\Controller\Api',
                        'action' => 'startjob',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'profond' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/secure',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Profond\Controller',
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller][/:action]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Profond\Controller',
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Profond\Controller',
                                'controller' => 'Index',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'machinedetails' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller][/:action][/:id]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Profond\Controller',
                                'controller' => 'Machine',
                                'action' => 'details',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                    'projectdetails' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller][/:action][/:id]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Profond\Controller',
                                'controller' => 'Project',
                                'action' => 'details',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                    'jobdetails' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller][/:action][/:id]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Profond\Controller',
                                'controller' => 'Job',
                                'action' => 'details',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                    'projectdelete' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller][/:action][/:id]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Profond\Controller',
                                'controller' => 'Project',
                                'action' => 'delete',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                    'jobdelete' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller][/:action][/:id]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Profond\Controller',
                                'controller' => 'Job',
                                'action' => 'delete',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                    'forcereloadconfigproject' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller][/:action][/:id]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Profond\Controller',
                                'controller' => 'Project',
                                'action' => 'forcereloadconfig',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                    'lanceurprepare' => array(
                        'type' => "Segment",
                        'options' => array(
                            'route' => '/[:controller][/:action][/:idproject][/:idexec]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Profond\Controller',
                                'controller' => 'Lanceur',
                                'action' => 'prepare',
                                'idproject' => '[0-9]+',
                                'idexec' => '[0-9]+',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                    'lanceurexecute' => array(
                        'type' => "Segment",
                        'options' => array(
                            'route' => '/[:controller][/:action][/:idproject][/:idexec]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Profond\Controller',
                                'controller' => 'Lanceur',
                                'action' => 'execute',
                                'idproject' => '[0-9]+',
                                'idexec' => '[0-9]+',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            //'Profond\Controller\Profond' => 'Profond\Controller\ProfondController',
            'Profond\Controller\Index' => 'Profond\Controller\IndexController',
            'Profond\Controller\Auth' => 'Profond\Controller\AuthController',
            'Profond\Controller\User' => 'Profond\Controller\UserController',
            'Profond\Controller\Machine' => 'Profond\Controller\MachineController',
            'Profond\Controller\Project' => 'Profond\Controller\ProjectController',
            'Profond\Controller\Job' => 'Profond\Controller\JobController',
            'Profond\Controller\Executable' => 'Profond\Controller\ExecutableController',
            'Profond\Controller\Lanceur' => 'Profond\Controller\LanceurController',
            'Profond\Controller\Api' => 'Profond\Controller\ApiController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_path_stack' => array(
            'profond' => __DIR__ . '/../view',
        ),
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'layout/header' => __DIR__ . '/../view/layout/header.phtml',
            'layout/sidebar' => __DIR__ . '/../view/layout/sidebar.phtml',
            'layout/quicksidebar' => __DIR__ . '/../view/layout/quicksidebar.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml'
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            'profond_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array ',
                'paths' => array(__DIR__ . '/../src/Profond/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Profond\Entity' => 'profond_entities'
                )
            )
        ),
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host' => 'localhost',
                    'port' => '3306',
                    'user' => 'root',
                    'password' => 'degrasse',
                    'dbname' => 'profond',
                ),
            ),
        ),
    ),
    'session' => array(
        'remember_me_seconds' => 2419200,
        'use_cookies' => true,
        'cookie_httponly' => true,
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'myapp',
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent',
        ),
    ),
    'viewhelpermanager' => array(
        'factories' => array(
            'ControllerName' => function ($sm) {
        $match = $sm->getServiceLocator()->get('application ')->getMvcEvent()->getRouteMatch();
        $viewHelper = new \Application\View\Helper\ControllerName($match);
        return $viewHelper;
    },
        ),
    ),
);
