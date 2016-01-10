<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Zend\Form\Annotation\AnnotationBuilder;
use Application\Form as Forms;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Zend\Mail as Mail;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class EventController extends AbstractActionController {

    protected $em;
    protected $authservice;

    public function onDispatch(MvcEvent $e) {
        /*
          $admin_session = new Container('admin');
          $username = $admin_session->username;
          if(empty($username)) {

          return $this->redirect()->toRoute('adminlogin');
          }
         */

        /* Set Default layout for all the actions */
        $this->layout('layout/layout');
        $em = $this->getEntityManager();
        $cities = $em->getRepository('\Admin\Entity\City')->findBy(array('countryId' => 2));
        $categories = $em->getRepository('\Admin\Entity\Categories')->findBy(array('status' => 1));
        $signupForm = new Forms\SignupForm();
        $loginForm = new Forms\LoginForm();
        $forgotpassForm = new Forms\ForgotPasswordForm();

        $this->layout()->signupForm = $signupForm;
        $this->layout()->loginForm = $loginForm;
        $this->layout()->forgotpassForm = $forgotpassForm;
        $this->layout()->cities = $cities;
        $this->layout()->categories = $categories;
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $city = "";
        $searchSession = new Container("searchsess");
        $searchType = "";
        $searchTerm = "";
        if ($searchSession->offsetExists("type")) {
            $searchType = $searchSession->offsetGet("type");
            $searchTerm = $searchSession->offsetGet("searchTerm");
        }
        if ($searchType == "category" && $searchTerm != "") {
            $this->layout()->searchedCategory = $searchTerm;
        }
        if ($searchType == "city" && $searchTerm != "") {
            $this->layout()->userCity = $searchTerm;
        }
        if (!empty($userid)) {
            $msg = 'You are already logged in.';
            $status = 1;
            $this->layout()->setVariable('userId', $user_session->userId);
            $this->layout()->setVariable('username', $user_session->userName);
            $username = $user_session->userName;
            $tmp_user = $em->getRepository('\Admin\Entity\Users')->find($user_session->userId);
            $city = $tmp_user->getCity();
            if ($searchType == "city" && $searchTerm != "") {
                $this->layout()->userCity = $searchTerm;
            } else {
                if (!empty($city)) {
                    $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                    $this->layout()->userCity = $cityObj->getCityName();
                }
            }
        } else {
            $this->layout()->setVariable('userId', '');
        }
        return parent::onDispatch($e);
    }

    public function setEntityManager(EntityManager $em) {
        $this->em = $em;
    }

    public function getEntityManager() {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }

    public function getAuthService() {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()
                    ->get('AuthService');
        }
        return $this->authservice;
    }

    public function searchAction() {
        $this->layout()->pageTitle = "Search Events";
        $em = $this->getEntityManager();
        $cities = $em->getRepository('\Admin\Entity\City')->findBy(array('countryId' => 2));
        $categories = $em->getRepository('\Admin\Entity\Categories')->findBy(array('status' => 1));
        $searchType = $this->params('type');
        $searchKey = $this->params('searchval');
        $limit = 2;
        $offset = $this->params()->fromQuery('offset');
        if ($offset == "") {
            $offset = 0;
        }
        $isScroll = $this->params()->fromQuery('isscroll');
        $commonPlugin = $this->Common();
        //echo $offset . "==". $limit ; 
        $events = $commonPlugin->getSearchEvents($searchType, $searchKey, $offset, $limit);
        $allEvents = $commonPlugin->getSearchEvents($searchType, $searchKey);
        $eventsCount = count($allEvents);
        //echo "==".count($events)."==".$eventsCount;//die;
        $uniques = array();
        $unique_artist = array();
        $unique_venue = array();
        /** Fetch Unique Artist From events * */
        foreach ($allEvents as $event) {
            $uniques[$event['artist']] = $event;
        }
        foreach ($uniques as $unique) {
            $unique_artist[] = $unique['artist'];
        }
        unset($uniques);
        $uniques = array();
        /** Fetch Unique Venue from events * */
        foreach ($allEvents as $event) {
            $uniques[$event['venue']] = $event;
        }
        foreach ($uniques as $unique) {
            $unique_venue[] = $unique['venue'];
        }
        /* Checking User session and fetch its set saved city */
        $user_session = new Container('user');
        $userId = $user_session->userId;
        if (!empty($userId)) {
            $tmp_user = $em->getRepository('\Admin\Entity\Users')->find($userId);
            $city = $tmp_user->getCity();
            if (!empty($city)) {
                $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                $this->layout()->userCity = $cityObj->getCityName();
            } else {
                $this->layout()->userCity = "";
            }
        } else {
            $this->layout()->userCity = "";
        }
        /* Checking User session and fetch its set saved city */
        $error_msg = "";
        $searchSession = new Container("searchsess");
        $searchSession->offsetSet('type', $searchType);
        $searchSession->offsetSet('searchTerm', $searchKey);
        $this->layout()->searchedCategory = "";
        switch ($searchType) {
            case 'city':
                if ($searchKey == "") {
                    $error_msg = "No city selected";
                } else {
                    $city = $em->getRepository('\Admin\Entity\City')->find($searchKey);
                    $searchtitle = 'Search results for “' . $city->getCityName() . '"';
                    $this->layout()->userCity = $city->getCityName();
                    $searchSession->offsetSet('searchTerm', $city->getCityName());
                }
                break;
            case 'category':
                if ($searchKey == "") {
                    $error_msg = "No category selected";
                } else {
                    $category = $em->getRepository('\Admin\Entity\Categories')->find($searchKey);
                    $searchtitle = 'Search results for “' . $category->getCategoryName() . '"';
                    $this->layout()->searchedCategory = $category->getCategoryName();
                    $searchSession->offsetSet('searchTerm', $category->getCategoryName());
                }
                break;
            case 'title':
                if ($searchKey == "") {
                    $error_msg = "No event selected";
                } else {
                    $searchtitle = 'Search results for “' . $searchKey . '"';
                }
                break;
            case 'artist':
                if ($searchKey == "") {
                    $error_msg = "No artist selected";
                } else {
                    $searchtitle = 'Search results for “' . $searchKey . '"';
                }
                break;
            case 'venue':
                if ($searchKey == "") {
                    $error_msg = "No Venue Selected";
                } else {
                    $searchtitle = 'Search results for “' . $searchKey . '"';
                }
                break;
            case 'results':
                $searchtitle = "All results";
                break;
        }
        $this->layout()->cities = $cities;
        $this->layout()->categories = $categories;
        if ($isScroll == 1) {
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('events' => $events, 'eventsCount' => $eventsCount, 'searchType' => $searchType, 'searchKey' => $searchKey, 'isScroll' => $isScroll))->setTerminal(true);
            return $viewModel;
        } else {
            return array('events' => $events, 'search_title' => $searchtitle,
                'error' => $error_msg, 'artists' => $unique_artist,
                'venus' => $unique_venue, 'limit' => $limit,
                'eventsCount' => $eventsCount, 'searchType' => $searchType,
                'searchKey' => $searchKey, 'isScroll' => $isScroll);
        }
    }

    /**
     * 
     * @return type
     */
    public function eventajaxsearchAction() {
        $this->layout()->pageTitle = "Tape Tickets :: Search Events";
        $em = $this->getEntityManager();
        $searchType = $this->params('type');
        $searchKey = $this->params('searchval');
        $result = array();
        switch ($searchType) {
            case "artist":
                $data = $em->getRepository('Admin\Entity\Event')->getEventsSearch($searchType, $searchKey);
                break;
            case "venues":
                $data = $em->getRepository('Admin\Entity\Event')->getEventsSearch($searchType, $searchKey);
                break;
            case "events":
            default:
                $data = $em->getRepository('Admin\Entity\Event')->getEventsSearch($searchType, $searchKey);
                break;
        }

        $i = 0;
        if (!empty($data)) {
            foreach ($data as $event) {
                switch ($searchType) {
                    case "artist":
                        $result[$i++] = $event->getEventArtist();
                        break;
                    case "venues":
                        $result[$i++] = $event->getEventVenueTitle();
                        break;
                    case "events":
                    default:
                        $result[$i++] = $event->getEventName();
                        break;
                }
            }
        }

        echo json_encode($result);
        exit;
    }

    /**
     * Displays Events related to particular category
     * @author Aditya
     */
    public function eventsbycategoryAction() {
        $this->layout()->pageTitle = " Category Events";
        echo "<pre>";
        print_r($tmpevent);
        die;
        return array('events' => $tmpevent, 'category' => $catarr);
    }

    /**
     * checkout- Action for the checkout of user
     * @return \Zend\View\Model\ViewModel
     * @author Manu Garg
     */
    public function checkoutAction() {
        $request = $this->getRequest();
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " Checkout";
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $em = $this->getEntityManager();
        if (empty($userId)) {
            /* if not logged in redirect the user to login page */
            return $this->redirect()->toRoute('home');
        } else {
            if ($request->isPost()) {
                $postedData = $request->getPost();
                $user_session->eventArray = $postedData; //add to the session
                $checkoutContainer = new Container("eventcheckout");
                $checkoutContainer->pdata = $postedData;
//                echo "<pre>";
//                print_r($postedData);
                if (empty($postedData)) {
                    $postedData = $request->getPost();
                    $checkoutContainer->pdata = $postedData;
//                    echo "<pre>";
//                    print_r($postedData);
                }
                //$data = $request->getPost();
                //echo "<pre>";
                //print_r($postedData);
                $eventObj = $em->getRepository('\Admin\Entity\Event')->getEvent($postedData['eventId']);
                $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $userId));
                if (empty($userObj)) {
                    die('User not found.');
                }
                $userCardDetails = $em->getRepository('Admin\Entity\UserCardDetails')->findBy(array('user' => $userObj));
                $billingObj = $em->getRepository('Admin\Entity\BillingAddress')->findBy(array('user' => $userObj));
                if (!empty($billingObj)) {
                    $billingObj = $billingObj[0];
                    $country = $billingObj->getCountry();
                } else {
                    $country = "";
                }
                $commonPlugin = $this->Common();
                $basePath = $commonPlugin->getBasePathOfProj();
                $form = new Forms\CheckoutForm($userCardDetails, $em, $country, $basePath);
                $form->get('email')->setValue($userObj->getEmail());
                $form->get('phoneno')->setValue($userObj->getPhone());
                if (!empty($billingObj)) {
                    $fname = $billingObj->getFirstName();
                    $lname = $billingObj->getLastName();
                    $street_addr = $billingObj->getAddress();
                    $country = $billingObj->getCountry();
                    $city = $billingObj->getCity();
                    $form->get('firstname')->setValue($fname);
                    $form->get('lastname')->setValue($lname);
                    $form->get('streetaddress')->setValue($street_addr);
                    if ($country != "") {
                        $form->get('country')->setValue($country);
                    }
                    if ($city != "") {
                        $form->get('city')->setValue($city);
                    }
                }
                $eventData = array();
                if (!empty($eventObj)) {
                    $eventData['id'] = $eventObj->getId();
                    $eventData['eventName'] = $eventObj->getEventName();
                    $eventData['eventArtist'] = $eventObj->getEventArtist();
                    $eventData['eventDesc'] = $eventObj->getEventDesc();
                    $eventData['eventVenueTitle'] = $eventObj->getEventVenueTitle();
                    $eventData['eventVenueIcon'] = $eventObj->getEventVenueIcon();
                    $eventData['eventImageBig'] = $eventObj->getEventImageBig();
                    $eventData['eventLink'] = $eventObj->getEventLink();
                    $eventData['eventCity'] = $eventObj->getEventCity()->getCityName();
                    $eventData['eventCountry'] = $eventObj->getEventCountry()->getCountryName();
                    $eventData['eventAddress'] = $eventObj->getEventAddress();
                    $eventOption = $eventObj->getEventOption();
                    $eventData['eventOption'] = array();
                    $dataArr = array();
                    if (!empty($eventOption)) {
                        $i = 0;
                        foreach ($eventOption as $option) {
                            $dataArr[$i++] = $option->getOption()->getId();
                        }
                    }
                    $eventData['eventOption'] = $dataArr;
                    $eventData['eventSchedule'] = $eventObj->getEventSchedule();
                    $eventData['latitude'] = $eventObj->getLatitude();
                    $eventData['longitude'] = $eventObj->getLongitude();
                    //$eventData['eventSeat'] = $eventObj->getEventSeat();
                    //$eventData['eventSeat'] = $em->getRepository('Admin\Entity\EventSeat')->findBy(array('event' => $eventObj, 'isDeleted' => 0));
                }
                $selectedSeats = json_decode($postedData['selectedSeats']);
                $num = 0;
                foreach ($selectedSeats as $select) {
                    foreach ($select->seatIds as $seatIds) {
                        $num ++;
                    }
                }
                $form->get('quantity')->setValue($num);
            } else {
                die('please book again');
            }
        }
        return new ViewModel(array(
            'userId' => $userId,
            'eventData' => $eventData,
            'checkoutContainer' => $checkoutContainer->pdata,
            'selectedSeats' => $selectedSeats,
            'form' => $form,
        ));
    }

    /**
     * checkoutinner- Action for the middle content of the checkout page as this is opening up in iframe
     * @return \Zend\View\Model\ViewModel
     * @author Manu Garg
     */
    public function checkoutinnerAction() {
        $viewModel = new ViewModel();
        $this->layout()->pageTitle = " Checkout";
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $request = $this->getRequest();
        if (empty($userId)) {
            /* if not logged in redirect the user to login page */
            //return $this->redirect()->toRoute('home');
            die('Please login to continue.');
        } else {
            $checkoutContainer = new Container("eventcheckout");
            //echo "<pre>"; print_r($checkoutContainer->pdata);die;
            $postedData = $checkoutContainer->pdata;
            //$checkoutContainer->setExpirationSeconds(60);
            $em = $this->getEntityManager();
            //print_r($postedData);
            $eventObj = $em->getRepository('\Admin\Entity\Event')->getEvent($postedData['eventId']);
            $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $userId));
            if (empty($userObj)) {
                die('User not found.');
            }
            $userCardDetails = $em->getRepository('Admin\Entity\UserCardDetails')->findBy(array('user' => $userObj));
            $billingObj = $em->getRepository('Admin\Entity\BillingAddress')->findBy(array('user' => $userObj));
            if (!empty($billingObj)) {
                $billingObj = $billingObj[0];
                $country = $billingObj->getCountry();
            } else {
                $country = "";
            }
            $commonPlugin = $this->Common();
            $basePath = $commonPlugin->getBasePathOfProj();
            $form = new Forms\CheckoutForm($userCardDetails, $em, $country, $basePath);
            $form->get('email')->setValue($userObj->getEmail());
            $form->get('phoneno')->setValue($userObj->getPhone());
            if (!empty($billingObj)) {
                $fname = $billingObj->getFirstName();
                $lname = $billingObj->getLastName();
                $street_addr = $billingObj->getAddress();
                $country = $billingObj->getCountry();
                $city = $billingObj->getCity();
                $form->get('firstname')->setValue($fname);
                $form->get('lastname')->setValue($lname);
                $form->get('streetaddress')->setValue($street_addr);
                if ($country != "") {
                    $form->get('country')->setValue($country);
                }
                if ($city != "") {
                    $form->get('city')->setValue($city);
                }
            }
            $eventData = array();
            if (!empty($eventObj)) {
                $eventData['id'] = $eventObj->getId();
                $eventData['eventName'] = $eventObj->getEventName();
                $eventData['eventArtist'] = $eventObj->getEventArtist();
                $eventData['eventDesc'] = $eventObj->getEventDesc();
                $eventData['eventVenueTitle'] = $eventObj->getEventVenueTitle();
                $eventData['eventVenueIcon'] = $eventObj->getEventVenueIcon();
                $eventData['eventImageBig'] = $eventObj->getEventImageBig();
                $eventData['eventLink'] = $eventObj->getEventLink();
                $eventData['eventCity'] = $eventObj->getEventCity()->getCityName();
                $eventData['eventCountry'] = $eventObj->getEventCountry()->getCountryName();
                $eventData['eventAddress'] = $eventObj->getEventAddress();
                $eventOption = $eventObj->getEventOption();
                $eventData['eventOption'] = array();
                $dataArr = array();
                if (!empty($eventOption)) {
                    $i = 0;
                    foreach ($eventOption as $option) {
                        $dataArr[$i++] = $option->getOption()->getId();
                    }
                }
                $eventData['eventOption'] = $dataArr;
                $eventData['eventSchedule'] = $eventObj->getEventSchedule();
                $eventData['latitude'] = $eventObj->getLatitude();
                $eventData['longitude'] = $eventObj->getLongitude();
                //$eventData['eventSeat'] = $eventObj->getEventSeat();
                $eventData['eventSeat'] = $em->getRepository('Admin\Entity\EventSeat')->findBy(array('event' => $eventObj, 'isDeleted' => 0));
                $ticketTypeArr = array();
                $id = 0;
                foreach ($eventData['eventSeat'] as $eventSeat) {
                    $ticketTypeArr[$id]['name'] = str_replace(" ", "_", $eventSeat->getTicketType());
                    $ticketTypeArr[$id]['price'] = $eventSeat->getSeatPrice();
                    $ticketTypeArr[$id]['currency'] = $eventSeat->getCurrency();
                    $id++;
                }
                $totalQty = 0;
                $bookedTickets = array();
                foreach ($ticketTypeArr as $ticket) {
                    $totalQty += $postedData[$ticket['name']];
                    if ($postedData[$ticket['name']] > 0) {
                        $bookedTickets[] = $ticket['name'];
                    }
                }
            }
            $form->get('quantity')->setValue($totalQty);
            $viewModel->setVariables(array('userId' => $userId,
                        'eventData' => $eventData,
                        'checkoutContainer' => $checkoutContainer->pdata,
                        'totalQty' => $totalQty,
                        'bookedTickets' => $bookedTickets,
                        'ticketTypeArr' => $ticketTypeArr,
                        'form' => $form,
                    ))
                    ->setTerminal(true);
            return $viewModel;
        }
    }

    public function checkouttimeoutAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " Checkout Expire";
        $checkoutContainer = new Container("eventcheckout");
        $checkOutData = $checkoutContainer->pdata;
        if (!empty($checkOutData)) {
            $checkoutContainer->getManager()->getStorage()->clear('pdata');
        }
        $eventId = $this->params('eventId');
        return new ViewModel(array('eventId' => $eventId));
    }

    public function checkouterrorAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " Checkout Error";
        $checkoutContainer = new Container("eventcheckout");
        $checkOutData = $checkoutContainer->pdata;
        if (!empty($checkOutData)) {
            $checkoutContainer->getManager()->getStorage()->clear('pdata');
        }
        $eventId = $this->params('eventId');
        return new ViewModel(array('eventId' => $eventId));
    }

    //Added by Shathish
    public function transactiondeclinedAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " transactiondeclined";
        $checkoutContainer = new Container("eventcheckout");
        $checkOutData = $checkoutContainer->pdata;
        if (!empty($checkOutData)) {
            $checkoutContainer->getManager()->getStorage()->clear('pdata');
        }
        $eventId = $this->params('eventId');
        return new ViewModel(array('eventId' => $eventId));
    }
    //Added by Shathish
    public function transactioncancelledAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " transactioncancelled";
        $checkoutContainer = new Container("eventcheckout");
        $checkOutData = $checkoutContainer->pdata;
        if (!empty($checkOutData)) {
            $checkoutContainer->getManager()->getStorage()->clear('pdata');
        }
        $eventId = $this->params('eventId');
        return new ViewModel(array('eventId' => $eventId));
    }
    //Added by Shathish
    public function transactionerrorAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " transactionerror";
        $checkoutContainer = new Container("eventcheckout");
        $checkOutData = $checkoutContainer->pdata;
        if (!empty($checkOutData)) {
            $checkoutContainer->getManager()->getStorage()->clear('pdata');
        }
        $eventId = $this->params('eventId');
        return new ViewModel(array('eventId' => $eventId));
    }

    /**
     * eventdetail - Showing the Details of an Event
     * @return \Zend\View\Model\ViewModel
     * @author Manu Garg
     */
    public function eventdetailAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = "Event Details";
        $em = $this->getEntityManager();
        $eventId = $this->params('eventId');
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $mainOptions = $em->getRepository('\Admin\Entity\MainOptions')->findAll();
        $eventObj = $em->getRepository('\Admin\Entity\Event')->find($eventId);
        $eventData = array();
        if (!empty($eventObj)) {
            $eventData['id'] = $eventObj->getId();
            $eventData['eventName'] = $eventObj->getEventName();
            $eventData['eventArtist'] = $eventObj->getEventArtist();
            $eventData['eventDesc'] = $eventObj->getEventDesc();
            $eventData['eventVenueTitle'] = $eventObj->getEventVenueTitle();
            $eventData['eventVenueIcon'] = $eventObj->getEventVenueIcon();
            $eventData['eventImageBig'] = $eventObj->getEventImageBig();
            $eventData['eventLink'] = $eventObj->getEventLink();
            $eventData['eventCity'] = $eventObj->getEventCity()->getCityName();
            $eventData['eventCountry'] = $eventObj->getEventCountry()->getCountryName();
            $eventData['eventAddress'] = $eventObj->getEventAddress();
            $eventData['latitude'] = $eventObj->getLatitude();
            $eventData['longitude'] = $eventObj->getLongitude();
            $eventOption = $eventObj->getEventOption();
            $eventData['eventOption'] = array();
            $dataArr = array();
            if (!empty($eventOption)) {
                $i = 0;
                foreach ($eventOption as $option) {
                    $dataArr[$i++] = $option->getOption()->getId();
                }
            }
            $eventData['eventOption'] = $dataArr;
            $eventData['eventSchedule'] = $eventObj->getEventSchedule();
        }
        return new ViewModel(array('eventData' => $eventData, 'mainOptions' => $mainOptions, 'userId' => $userId));
    }

    //Aded by Yesh
    public function ajaxeventmapAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $id;
        $eventId;
        $mapObject;
        if ($request->isPost()) {
            $data = $request->getPost();
            $eventId = $data['eventID'];
            //$layoutID = $data['layoutID']; //not in used, may be future
            $mapObj = $em->getRepository('Admin\Entity\EventMap')->getMapByEventId($eventId);
            //$zoneObj = $em->getRepository('Admin\Entity\MapZone')->getZoneByEventId($eventId);
            foreach ($mapObj as $obj) {
                $id = $obj['id'];
                $eventId = $obj['eventId'];
                $mapObject = $obj['mapObject'];
            }
            $mapObject = unserialize($mapObject); //unserialize object
            print json_encode(array('status' => 'success', 'id' => $id, 'eventId' => $eventId, 'mapObject' => $mapObject));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }

    public function ajaxgeteventscheduleidAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        if ($request->isPost()) {
            $data = $request->getPost();
            $eventID = $data['eventID'];
            $eventDate = $data['eventDate'];
            $scheduleID = $em->getRepository('Admin\Entity\EventSchedule')->getEventScheduleIdByEventDate($eventID, $eventDate);
            print json_encode(array('status' => 'success', 'scheduleID' => $scheduleID));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }

    public function ajaxgetseatstatus() {
        //
    }

    public function ajaxgetavailableseatsAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        if ($request->isPost()) {
            $data = $request->getPost();
            $seatLabel = $data['clickID'];
            $eventID = $data['eventID'];
            $scheduleID = $data['scheduleID'];
            $zoneTitle = $data['zoneTitle'];
            $userIDInfo = $data['userID'];
            $zone = $em->getRepository('Admin\Entity\MapZone')->getZoneByTitle($zoneTitle, $eventID);
            $zoneID = $zone['id'];
            $zoneSeats = $em->getRepository('Admin\Entity\ZoneSeats')->getSeatStatus($zoneID, $scheduleID);
            $available = 0;
            if ($seatLabel !== "") {
                foreach ($zoneSeats as $zoneSeat) {
                    if ($seatLabel === $zoneSeat['seatLabel']) {
                        $available = $zoneSeat['seatAvailability'];
                        $selectedUserId = $zoneSeat['userId'];
                        $em->getRepository('Admin\Entity\ZoneSeats')->updateSelectedSeat($zoneID, $scheduleID, $seatLabel, 2, 0, $userIDInfo);
                    }
                }
            }
            print json_encode(array('status' => 'success', 'zoneSeats' => $zoneSeats, 'zone' => $zone, 'available' => $available, 'selectedUserId' => $selectedUserId ));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }

    public function ajaxremoveselectionAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        if ($request->isPost()) {
            $data = $request->getPost();
            $eventID = $data['eventID'];
            $scheduleID = $data['scheduleID'];
            $titleObj = json_decode($data['titleObj']);

            foreach ($titleObj as $row) {
                $zoneTitle = $row->zoneTitle;
                $zone = $em->getRepository('Admin\Entity\MapZone')->getZoneByTitle($zoneTitle, $eventID);
                $zoneID = $zone['id'];
                $seatIds = $row->seatIds;
                foreach ($seatIds as $seatId) {
                    $seatIds = explode("_", $seatId);
                    $seatLabel = $seatIds[3]."_".$seatIds[4];
                    $em->getRepository('Admin\Entity\ZoneSeats')->unselectZoneSeats($zoneID, $eventID, $scheduleID, $seatLabel);
                }
            }
            print json_encode(array('status' => 'success'));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }

    public function ajaxunselectseatbookingAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        if ($request->isPost()) {
            $data = $request->getPost();
            $eventID = $data['eventID'];
            $zoneTitle = $data['zoneTitle'];
            $seatLabel = $data['clickID'];
            $scheduleID = $data['scheduleID'];
            $zone = $em->getRepository('Admin\Entity\MapZone')->getZoneByTitle($zoneTitle, $eventID);
            $zoneID = $zone['id'];
            $em->getRepository('Admin\Entity\ZoneSeats')->unselectZoneSeats($zoneID, $eventID, $scheduleID, $seatLabel);
            print json_encode(array('status' => 'success'));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }

    //Added by Yesh

    /**
     * geteventseatdetailsajaxAction - This is an AJAX action for fetching the 
     * seat details at event detail page.
     * @return \Zend\View\Model\ViewModel
     * @author Manu Garg
     */
    public function geteventseatdetailsajaxAction() {
        $request = $this->getRequest();
        $viewModel = new ViewModel();
        $user_session = new Container('user');
        $userId = $user_session->userId;
        if ($request->isPost()) {
            $postedData = $request->getPost();
            $em = $this->getEntityManager();
            $eventObj = $em->getRepository('\Admin\Entity\Event')->getEvent($postedData['eventId']);
            $eventData = array();
            if (!empty($eventObj)) {
                $eventData['id'] = $eventObj->getId();
                $eventData['eventName'] = $eventObj->getEventName();
                $eventData['eventArtist'] = $eventObj->getEventArtist();
                $eventData['eventDesc'] = $eventObj->getEventDesc();
                $eventData['eventVenueTitle'] = $eventObj->getEventVenueTitle();
                $eventData['eventVenueIcon'] = $eventObj->getEventVenueIcon();
                $eventData['eventImageBig'] = $eventObj->getEventImageBig();
                $eventData['eventLink'] = $eventObj->getEventLink();
                $eventData['eventCity'] = $eventObj->getEventCity()->getCityName();
                $eventData['eventCountry'] = $eventObj->getEventCountry()->getCountryName();
                $eventData['eventAddress'] = $eventObj->getEventAddress();
                $eventOption = $eventObj->getEventOption();
                $eventData['eventOption'] = array();
                $dataArr = array();
                if (!empty($eventOption)) {
                    $i = 0;
                    foreach ($eventOption as $option) {
                        $dataArr[$i++] = $option->getOption()->getId();
                    }
                }
                $eventData['eventOption'] = $dataArr;
                $eventData['eventSchedule'] = $eventObj->getEventSchedule();
                $eventData['latitude'] = $eventObj->getLatitude();
                $eventData['longitude'] = $eventObj->getLongitude();
                $eventData['event_seat'] = $em->getRepository('Admin\Entity\EventSeat')->findBy(array('event' => $eventObj, 'isDeleted' => 0));
            }
            $viewModel->setVariables(array('postedData' => $postedData, 'eventData' => $eventData, 'userId' => $userId))->setTerminal(true);
            return $viewModel;
        }
        $viewModel->setVariables(array('postedData' => "", 'eventData' => "", 'userId' => $userId))->setTerminal(true);
        return $viewModel;
    }

//    public function confirmorderAction() {
//        $this->layout('layout/eventlayout');
//        $this->layout()->pageTitle = "Confirm Order";
//        $request = $this->getRequest();
//        $user_session = new Container('user');
//        $userId = $user_session->userId;
//        $em = $this->getEntityManager();
//        //test
//        $post = ['post', 'posted', array('poting' => 'postedd')];
//        $noPost = ['nopost', 'posted', array('poting' => 'postedd')];
//        //test
//        if ($request->isPost()) {
//            $data = $request->getPost();
//            echo "<pre>";
//            print_r($data);
//        }
//    }
    public function confirmorderAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = "Confirm Order";
        $request = $this->getRequest();
        $user_session = new Container('user');
        $userId = $user_session->userId;
        //$id = $this->params('cardid');        
        $em = $this->getEntityManager();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        if ($request->isPost()) {
            $checkoutContainer = new Container("eventcheckout");
            $data = $checkoutContainer->pdata;
            if (empty($data)) {
                die("Requested Data not found.");
            } else {
                //print_r($data);
                $postedData = $request->getPost();
                // print_r($postedData);
                $eventObj = $em->getRepository('Admin\Entity\Event')->findOneBy(array('id' => $data['eventId'], 'status' => 1));
                $userObj = $em->getRepository('Admin\Entity\Users')->find($userId);
                $eventSeatData = $em->getRepository('Admin\Entity\EventSeat')->findBy(array('event' => $eventObj, 'isDeleted' => 0));
                $commonPlugin = $this->Common();
                
                $userBookingObj = new Entities\UserBooking();
                $userBookingObj->setUser($userObj);
                $userBookingObj->setEvent($eventObj);
                $userBookingObj->setEventDate(date_create(date("Y-m-d", strtotime($data['eventDate']))));
                $userBookingObj->setEventTime(date_create(date("H:i:s", strtotime($data['eventTime']))));
                $userBookingObj->setBookingSeatCount($postedData['quantity']);
                $userBookingObj->setBookingTotalPrice($data['totalAmount']);
                $userBookingObj->setEmail($postedData['email']);
                $userBookingObj->setPhoneNo($postedData['phoneno']);
                /* $userBookingObj->setCardType($postedData['card_type']);
                  $userBookingObj->setCardNo($postedData['cardno']);
                  $userBookingObj->setExpiryMonth($postedData['month']);
                  $userBookingObj->setExpiryYear($postedData['year']); */
                $userBookingObj->setFirstName($postedData['firstname']);
                $userBookingObj->setLastName($postedData['lastname']);
                $userBookingObj->setStreetAddress($postedData['streetaddress']);
                $userBookingObj->setBookingOrderNo("");
                $userBookingObj->setCity($postedData['city']);
                $userBookingObj->setCountry($postedData['country']);
                $userBookingObj->setStatus(2);
                $userBookingObj->setBookingMadeDate(date_create(date('Y-m-d H:i:s')));
                $em->persist($userBookingObj);
                $em->flush();
                $orderId = $userBookingObj->getId();
//              
                $schemeHost = $commonPlugin->getSchemeHostOfProj();
                $htpconfirmatinPath = $this->url()->fromRoute('htpconfirmation');
                $acceptUrl = $schemeHost . $htpconfirmatinPath;
                $conf = array();
                $securityKey = "tapetickets123#";
                $conf['accountPspId'] = "testclassic";
                $conf['parametersAcceptUrl'] = $acceptUrl;
                $conf['parametersExceptionUrl'] = $acceptUrl;
                $conf['paymentMethod'] = "CreditCard";
                $conf['layoutLanguage'] = "en_EN";
                $conf['aliasId'] = "";
                $conf["aliasOrderId"] = $orderId;
                $conf["aliasStorePermanently"] = "N";
                if ($conf["aliasId"] == "") {
                    $paramString = "ACCOUNT.PSPID=" . $conf['accountPspId'] . $securityKey . "ALIAS.ORDERID=" . $conf['aliasOrderId'] . $securityKey . "ALIAS.STOREPERMANENTLY=" . $conf["aliasStorePermanently"] . $securityKey . "CARD.PAYMENTMETHOD=" . $conf['paymentMethod'] . $securityKey . "LAYOUT.LANGUAGE=" . $conf['layoutLanguage'] . $securityKey . "PARAMETERS.ACCEPTURL=" . $conf['parametersAcceptUrl'] . $securityKey . "PARAMETERS.EXCEPTIONURL=" . $conf['parametersExceptionUrl'] . $securityKey;
                } else {
                    //$paramstring = "ACCOUNT.PSPID=testclassicclassicinformatics123#ALIAS.ALIASID=C2BCA5D2-35DF-4BBD-A68E-E924F4BD5515classicinformatics123#ALIAS.ORDERID=1459classicinformatics123#CARD.PAYMENTMETHOD=CreditCardclassicinformatics123#LAYOUT.LANGUAGE=en_ENclassicinformatics123#PARAMETERS.ACCEPTURL=http://tapetickets.demos.classicinformatics.com/tmp/index.phpclassicinformatics123#PARAMETERS.EXCEPTIONURL=http://tapetickets.demos.classicinformatics.com/tmp/index.phpclassicinformatics123#";
                    $paramString = "ACCOUNT.PSPID=" . $conf['accountPspId'] . $securityKey . "ALIAS.ALIASID=" . $conf['aliasId'] . $securityKey . "ALIAS.ORDERID=" . $conf['aliasOrderId'] . $securityKey . "ALIAS.STOREPERMANENTLY=" . $conf["aliasStorePermanently"] . $securityKey . "CARD.PAYMENTMETHOD=" . $conf['paymentMethod'] . $securityKey . "LAYOUT.LANGUAGE=" . $conf['layoutLanguage'] . $securityKey . "PARAMETERS.ACCEPTURL=" . $conf['parametersAcceptUrl'] . $securityKey . "PARAMETERS.EXCEPTIONURL=" . $conf['parametersExceptionUrl'] . $securityKey;
                }
                $conf['sha1'] = sha1($paramString);
                $conf['sha1'] = strtoupper($conf['sha1']);
                
            }
           
        }
        
        return new ViewModel(array('orderId' => $orderId, 'postedData' => $postedData, 'userObj' => $userObj, 'data' => $data, 'conf' => $conf));
        
    }

    //Update by Shathish
    public function htpconfirmationAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = "Confirm Order";
        $checkoutContainer = new Container("eventcheckout");
        $commonPlugin = $this->Common();
        $data = $checkoutContainer->pdata;
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        if (empty($data)) {
            die("Requested Data not found.");
        } else {
            $postData = $this->params()->fromPost();
            //print_r($postData);
            $getData = $this->params()->fromQuery();
            //print_r($getData);
            $requestData = array_merge($postData, $getData);
            $checkoutContainer->htpresponse = $requestData;

            /*
             * HTP(Host Tokenization Page) response example
             * Array ( [status] => 0 [OrderID] => 39 [NCError] => 0 [NCErrorCN] => 0 
             * [NCErrorCardNo] => 0 [NCErrorCVC] => 0 [NCErrorED] => 0 [CardNo] => XXXXXXXXXXXX0002 
             * [Alias] => B9113DCA-768A-4392-B0D5-ADE35E3194E9 [Brand] => VISA [CN] => testcard 
             * [CVC] => XXX [ED] => 0317 [StorePermanently] => Y 
             * [SHASign] => 4F0CA4137050115FEAE1985E8D3788BD91DB9A25 )
             */
           
            /* Result of the alias creation:
              0=OK
              1=NOK
              2=Alias updated
              3=Cancelled by user
             */
            if ($requestData['Alias_Status'] == 3){
                    return $this->redirect()->toRoute('transactioncancelled', array('eventId' => $checkPageData['eventId'],));
            }
            if ($requestData['status'] == 0 || $requestData['status'] == 3) {
                /* If Alias creation is successful then we need to send it to 2nd request */
                $user_session = new Container('user');
                $userId = $user_session->userId;
                $checkoutContainer = new Container("eventcheckout");
                $checkPageData = $checkoutContainer->pdata;
                $em = $this->getEntityManager();
                $userBookingObj = $em->getRepository('Admin\Entity\UserBooking')->findOneBy(array('user' => $userId, 'event' => $checkPageData['eventId'], 'id' => $requestData['OrderID']));
                $userBookingObj->setCardType($requestData['Brand']);
                $userBookingObj->setCardNo($requestData['CardNo']);

                
                /*
                  Commenting this code here as no need to save card month year here
                  $ed = $requestData['ED'];
                  $ed_m = substr($ed,0,2);
                  $ed_y = substr($ed,2,2);
                  $userBookingObj->setExpiryMonth($ed_m);
                  $userBookingObj->setExpiryYear($ed_y);
                 */
                $em->flush();
                $schemeHost = $commonPlugin->getSchemeHostOfProj();
                $paymentGatewayReturnPath = $this->url()->fromRoute('paymentgatewayreturn');
                $acceptUrl = $schemeHost . $paymentGatewayReturnPath;
                $amount = $data['totalAmount'] * 100;
                $postData = array(
                    "ACCEPTURL" => $acceptUrl,
                    "ALIAS" => $requestData['Alias'],
                    "AMOUNT" => $amount,
                    "CURRENCY" => "AED",
                    "COM" => "Ticket (s) for Order Id " . $requestData['OrderID'] . "is confirmed",
                    //"CVC"       => "123", /* Need to make it dynamic */
                    "DECLINEURL" => $acceptUrl,
                    "EMAIL" => $userBookingObj->getEmail(),
                    "EXCEPTIONURL" => $acceptUrl,
                    "FLAG3D" => "Y",
                    "ORDERID" => $requestData['OrderID'],
                    "PSPID" => 'testclassic',
                    "PSWD" => "tapetickets#123",
                    "USERID" => "manugarg",
                    "WIN3DS" => "MAINW",
                );
                //print_r($postData);
                $singleString = "";
                $secretKey = "tapetickets123#";
                foreach ($postData as $key => $value) {
                    $singleString .= $key . '=' . $value . $secretKey;
                }
                //echo $singleString;
                $sha1String = sha1($singleString);
                $sha1String = strtoupper($sha1String);
                $postData["SHASIGN"] = $sha1String;
                $fields_string = '';
                foreach ($postData as $key => $value) {
                    $fields_string .= strtoupper($key) . '=' . $value . '&';
                }
                $fields_string = rtrim($fields_string, '&');
                //echo "<br />";
                //echo $fields_string;
                $cSession = curl_init();
                //step2
                curl_setopt($cSession, CURLOPT_URL, "https://secure.payfort.com/ncol/test/orderdirect.asp");
                curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($cSession, CURLOPT_POST, true);
                curl_setopt($cSession, CURLOPT_POSTFIELDS, $fields_string);
                curl_setopt($cSession, CURLOPT_HEADER, false);
                //step3
                $result = curl_exec($cSession);
                //step4
                curl_close($cSession);
                //step5
                //print_r($result);
                $xml = simplexml_load_string($result);
                if ($xml) {
                    $checkoutContainer->paymentconfirmation = array(
                        "ORDERID" => (string) $xml['orderID'],
                        "PAYID" => (string) $xml['PAYID'],
                        "NCSTATUS" => (string) $xml['NCSTATUS'],
                        "NCERROR" => (string) $xml['NCERROR'],
                        "ACCEPTANCE" => (string) $xml['ACCEPTANCE'],
                        "STATUS" => (string) $xml['STATUS'],
                        "IPCTY" => (string) $xml['IPCTY'],
                        "CCCTY" => (string) $xml['CCCTY'],
                        "ECI" => (string) $xml['ECI'],
                        "CVCCheck" => (string) $xml['CVCCheck'],
                        "AAVCheck" => (string) $xml['AAVCheck'],
                        "VC" => (string) $xml['VC'],
                        "AMOUNT" => (string) $xml['amount'],
                        "CURRENCY" => (string) $xml['currency'],
                        "PM" => (string) $xml['PM'],
                        "BRAND" => (string) $xml['BRAND'],
                        "NCERRORPLUS" => (string) $xml['NCERRORPLUS'],
                    );
                    $htmlstatus = (string) $xml['STATUS'];
                    if ((string) $xml['NCERRORPLUS'] == "CARD REFUSED") {
                        return $this->redirect()->toRoute('transactiondeclined', array('eventId' => $checkPageData['eventId'],));
                    }

                    $htmlData = '';
                    if ($htmlstatus == "9") {
                        return $this->redirect()->toRoute('paymentgatewayreturn', array('shasign' => $getData['SHASign'],));
                    } else if ($htmlstatus == "46") {
                        foreach ($xml as $child => $value) {
                            if ($child == "HTML_ANSWER") {
                                /* If the card supports the 3D verification then this condition will run */
                                $htmlData = base64_decode($value);
                                $viewModel->setVariables(array('htmlData' => $htmlData));
                                return $viewModel;
                            }
                        }
                    }
                } else {
                     return $this->redirect()->toRoute('transactionerror', array('eventId' => $checkPageData['eventId'],));
                }
            } else {
                 return $this->redirect()->toRoute('transactionerror', array('eventId' => $checkPageData['eventId'],));
            }
        }
    }

    //get Shaw hash value of parmaeters strings
    protected function getRawSHA($parameters, $passphrase) {
        ksort($parameters);
        // string to be encoded
        $params = array();
        unset($parameters['SHASIGN']);
        // add required params to our digest
        foreach ($parameters as $key => $value) {
            if ($value != '') {
                $params[$key] = mb_strtoupper($key) . '=' . $value;
            }
        }
        // alphabetically, based on keys
        ksort($params);
        // add secret key and return
        return implode($passphrase, $params) . $passphrase;
    }

    //get Shaw of strings
    public function getSHA1($sha) {
        return mb_strtoupper(sha1($sha));
    }

    /**
     * update User Selecte dSeats
     * @param type $bookingId
     * Added by Yesh
     */
    private function updateUserSelectedSeats($bookingId) {
        $em = $this->getEntityManager();
        $user_session = new Container('user');
        $selectedSeats = json_decode($user_session->eventArray['selectedSeats']);
        foreach ($selectedSeats as $row) {
            $zoneTitle = $row->zoneTitle;
            $mapZoneObj = $em->getRepository('Admin\Entity\MapZone')->findOneBy(array('zoneTitle' => $zoneTitle, 'eventId' => $user_session->eventArray['eventId']));
            $zoneId = $mapZoneObj->getId(); //get zone Id
            $seatIds = $row->seatIds;
            foreach ($seatIds as $seatLabel) {
                $seatLabel = explode("_", $seatLabel);
                $seatLabel = $seatLabel[3]."_".$seatLabel[4];
                $zoneSeatsObj = $em->getRepository('Admin\Entity\ZoneSeats')->findOneBy(array(
                    'scheduleId' => $user_session->eventArray['scheduleId'],
                    'eventId' => $user_session->eventArray['eventId'],
                    'zoneId' => $zoneId,
                    'seatLabel' => $seatLabel
                ));
                $zoneSeatsObj->setSeatAvailability(1);
                $zoneSeatsObj->setBookingId($bookingId);
                $zoneSeatsObj->setUserId($user_session->eventArray['userId']);
                $em->persist($zoneSeatsObj);
                $em->flush();
            }
        }
    }

    public function orderAction() {
        $em = $this->getEntityManager();
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $checkoutContainer = new Container("eventcheckout");
        $commonPlugin = $this->Common();
        $checkPageData = $checkoutContainer->pdata;
        $htpResponse = $checkoutContainer->htpresponse;
        $paymentConfirmation = $checkoutContainer->paymentconfirmation;
        $schemeHost = $commonPlugin->getSchemeHostOfProj();
        $ticketPreviewPath = $this->url()->fromRoute('checkouterror', array('eventId' => $checkPageData['eventId']));
        $redirectUrl = $schemeHost . $ticketPreviewPath;
        if (empty($checkPageData) || empty($htpResponse) || empty($paymentConfirmation)) {
            die("Requested Data not found.");
        } else {
            $postData = $this->params()->fromPost();
            //print_r($postData);
            $getData = $this->params()->fromQuery();
            //print_r($getData);
            $requestData = array_merge($postData, $getData); /* If data received after entering in 3-D */
            $finalResponse = $requestData;
            //print_r($finalResponse);
            //die;
            $this->shaOut = "tapetickets123#";
            if (isset($finalResponse) && !empty($finalResponse)) {
                $params = $this->getRawSHA(array_change_key_case($finalResponse, CASE_UPPER), $this->shaOut);
                $sha = $this->getSHA1($params);
                $checksign = $finalResponse['SHASIGN'];
                // doublecheck SHA digest
                if ($sha <> $checksign) {
                    echo "Sha Sign in and out does not matches";
                    exit;
                }
            }
            $userBookingObj = $em->getRepository('Admin\Entity\UserBooking')->findOneBy(array('user' => $userId, 'event' => $checkPageData['eventId'], 'id' => $htpResponse['OrderID']));
            $bookingId = $userBookingObj->getId(); //get the booked id
            if (empty($userBookingObj)) {
                unset($user_session->eventArray); //unset event Array
                $ticketPreviewPath = $this->url()->fromRoute('checkouterror', array('eventId' => $checkPageData['eventId']));
                $redirectUrl = $schemeHost . $ticketPreviewPath;
            }
            if (!empty($finalResponse)) {
                /* if Card is 3-D means if user is asked for password for the payment */
                $checkoutContainer->finalResponse = $finalResponse;
                if ($finalResponse['STATUS'] != 0 && $finalResponse['STATUS'] != 2) {
                    $ticketPreviewPath = $this->url()->fromRoute('ticketpreview', array('bookingid' => $finalResponse['orderID']));
                    $redirectUrl = $schemeHost . $ticketPreviewPath;
                    $userBookingObj->setPayId($finalResponse['PAYID']);
                    $userBookingObj->setStatus(1);
                    $em->persist($userBookingObj);
                    $em->flush();
                    $this->updateUserSelectedSeats($bookingId);
                    $user_session->orderStatus = TRUE;
                }
            } elseif ($paymentConfirmation['STATUS'] != 0 && $paymentConfirmation['STATUS'] != 2) {
                /* if Card is not 3-D means if user is not asked for password for the payment */
                $ticketPreviewPath = $this->url()->fromRoute('ticketpreview', array('bookingid' => $paymentConfirmation['ORDERID']));
                $redirectUrl = $schemeHost . $ticketPreviewPath;
                $userBookingObj->setPayId($paymentConfirmation['PAYID']);
                $userBookingObj->setStatus(1);
                $em->persist($userBookingObj);
                $em->flush();
                $this->updateUserSelectedSeats($bookingId);
                $user_session->orderStatus = TRUE;
            } else {
                unset($user_session->eventArray); //unset event Array
                $ticketPreviewPath = $this->url()->fromRoute('checkouterror', array('eventId' => $checkPageData['eventId']));
                $redirectUrl = $schemeHost . $ticketPreviewPath;
            }
            $viewModel->setVariables(array('redirectUrl' => $redirectUrl));
        }
        unset($user_session->eventArray); //unset event Array
        return $viewModel;
    }

}
