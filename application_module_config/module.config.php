<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
            'uniqueemail' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/checkemail/[:email]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'checkemail',
                    ),
                ),
            ),
            'eventsearch' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/events/[:type]/[:searchval]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'search',
                    ),
                ),
            ),
            //Added by Yesh
            'generatebarcode' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user/generatebarcode/[:barcode]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'generatebarcode',
                    ),
                ),
            ),
            'ajaxeventmap' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/ajaxeventmap',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'ajaxeventmap',
                    ),
                ),
            ),
            'ajaxgeteventscheduleid' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/ajaxgeteventscheduleid',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'ajaxgeteventscheduleid',
                    ),
                ),
            ),
            'ajaxgetavailableseats' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/ajaxgetavailableseats',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'ajaxgetavailableseats',
                    ),
                ),
            ),
            'ajaxremoveselection' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/ajaxremoveselection',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'ajaxremoveselection',
                    ),
                ),
            ),
            'ajaxunselectseatbooking' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/ajaxunselectseatbooking',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'ajaxunselectseatbooking',
                    ),
                ),
            ),
            //Added by Yesh
            'eventajaxsearch' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/ajaxsearch/[:type]/[:searchval]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'eventajaxsearch',
                    ),
                ),
            ),
            'eventdetail' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/eventdetail/[:eventId]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'eventdetail',
                    ),
                ),
            ),
            'eventseatsdetailajax' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/geteventseatdetailsajax',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'geteventseatdetailsajax',
                    ),
                ),
            ),
            'userlogin' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/index/userlogin',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'userlogin',
                    ),
                ),
            ),
            'userlogout' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/index/userlogout',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'userlogout',
                    ),
                ),
            ),
            'changepassword' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/changepassword',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'userchangepassword',
                    ),
                ),
            ),
            'basicinfo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/basicinfo',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'userbasicinfo',
                    ),
                ),
            ),
            'userlocationinfo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/userlocationinfo',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'userlocation',
                    ),
                ),
            ),
            'forgotpassword' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/forgotpassword',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'userforgotpassword',
                    ),
                ),
            ),
            'ajaxuserlogin' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/index/ajaxuserlogin',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'validatepostajax',
                    ),
                ),
            ),
            'facebooklogin' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/fblogin',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'fblogin',
                    ),
                ),
            ),
            'fbconnect' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/fbconnect',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'connect',
                    ),
                ),
            ),
            'aboutus' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/aboutus',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'aboutus',
                    ),
                ),
            ),
            'terms' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/terms',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'terms',
                    ),
                ),
            ),
            'faq' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/faq',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'faq',
                    ),
                ),
            ),
            'privacypolicy' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/privacypolicy',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'privacypolicy',
                    ),
                ),
            ),
            'deliverypolicy' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/deliverypolicy',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'deliverypolicy',
                    ),
                ),
            ),
            'printingticket' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/printingticket',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'printingticket',
                    ),
                ),
            ),
            'contactus' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/contactus',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'contactus',
                    ),
                ),
            ),
            'support' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/support',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'support',
                    ),
                ),
            ),
            'userprofile' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/userprofile',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'userprofile',
                    ),
                ),
            ),
            'getcity' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user/getcity/[:countryId]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'getcity',
                    ),
                ),
            ),
            'geteventdata' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/geteventdata',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'getSearchableEvents',
                    ),
                ),
            ),
            'getartist' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/getartist/[:artistname]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'getArtist',
                    ),
                ),
            ),
            'payment-details' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/user/payment-details/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'paymentdetails',
                    ),
                ),
            ),
            'userbillinginfo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/user/billinginfo/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'userbillinginfo',
                    ),
                ),
            ),
            'usercardinfo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/user/cardinfo/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'usercardinfo',
                    ),
                ),
            ),
            'addcard' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user/addcard/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'addCard',
                    ),
                ),
            ),
            'updatecard' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/user/updatecard/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'updateCardDetails',
                    ),
                ),
            ),
            'getcard' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/getcarddetails/[:cardid]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'getCardDetails',
                    ),
                ),
            ),
            'checkout' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/checkout',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'checkout',
                    ),
                ),
            ),
            'checkoutinner' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/checkoutinner',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'checkoutinner',
                    ),
                ),
            ),
            'htpconfirmation' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/htpconfirmation',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'htpconfirmation',
                    ),
                ),
            ),
            'checkouttimeout' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/checkouttimeout[/:eventId]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'checkouttimeout',
                    ),
                ),
            ),
            'checkouterror' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/checkouterror[/:eventId]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'checkouterror',
                    ),
                ),
            ),
            //added by Shathish
            'transactiondeclined' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/transactiondeclined[/:eventId]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'transactiondeclined',
                    ),
                ),
            ),
            'transactioncancelled' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/transactioncancelled[/:eventId]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'transactioncancelled',
                    ),
                ),
            ),
            'transactionerror' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/transactionerror[/:eventId]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'transactionerror',
                    ),
                ),
            ),
            'confirmorder' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/confirmorder',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'confirmorder',
                    ),
                ),
            ),
            'paymentgatewayreturn' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/event/order',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Event',
                        'action' => 'order',
                    ),
                ),
            ),
            'ticketpreview' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user/ticketpreview/[:bookingid]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'ticketpreview',
                    ),
                ),
            ),
            'ticketpdf' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/ticketpdf/[:bookingid]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'ticketpdf',
                    ),
                ),
            ),
            'my-event' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/user/my-event/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'myevent',
                    ),
                ),
            ),
            'order-history' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/user/order-history/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'orderhistory',
                    ),
                ),
            ),
            'printticket' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/print-ticket/[:bookingid]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action' => 'printticket',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Event' => 'Application\Controller\EventController',
            'Application\Controller\User' => 'Application\Controller\UserController'
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'Common' => 'Application\Controller\Plugin\Common',
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'Commonviewhelper' => 'Application\View\Helper\Commonviewhelper'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'layout/eventlayout' => __DIR__ . '/../view/layout/eventlayout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
