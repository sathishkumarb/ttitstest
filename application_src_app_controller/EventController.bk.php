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
            $viewModel->setVariables(array('events' => $events,
                        'eventsCount' => $eventsCount, 'searchType' => $searchType,
                        'searchKey' => $searchKey, 'isScroll' => $isScroll))
                    ->setTerminal(true);
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
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " Checkout";
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $request = $this->getRequest();
        /* $checkoutContainer = new Container("eventcheckout");
          if(!empty($checkoutContainer->pdata)){
          //print_r($checkoutContainer->pdata);
          //die('111')
          } */
        if (empty($userId)) {
            /* if not logged in redirect the user to login page */
            //return $this->redirect()->toRoute('home');
            die('Please login to continue.');
        } else {

            //$this->layout()->setVariable('userId', $user_session->userId);
            //$this->layout()->setVariable('username', $user_session->userName);
            //$username = $user_session->userName;            
            if ($request->isPost()) {
                $postedData = $request->getPost();
                $checkoutContainer = new Container("eventcheckout");
                $checkoutContainer->pdata = $postedData;
                //$checkoutContainer->setExpirationSeconds(60);
                /* $em = $this->getEntityManager();

                  //print_r($postedData);
                  $eventObj = $em->getRepository('\Admin\Entity\Event')->getEvent($postedData['eventId']);
                  $userObj   = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status'=>1,'userType'=>'N','id'=>$userId));
                  if(empty($userObj)){
                  die('User not found.');
                  }
                  $userCardDetails = $em->getRepository('Admin\Entity\UserCardDetails')->findBy(array('user'=>$userObj));
                  $billingObj = $em->getRepository('Admin\Entity\BillingAddress')->findBy(array('user'=>$userObj));

                  if(!empty($billingObj)){
                  $billingObj = $billingObj[0];
                  $country = $billingObj->getCountry();
                  }else{
                  $country = "";
                  }
                  $commonPlugin = $this->Common();
                  $basePath = $commonPlugin->getBasePathOfProj();
                  $form = new Forms\CheckoutForm($userCardDetails,$em, $country, $basePath);
                  $form->get('email')->setValue($userObj->getEmail());
                  $form->get('phoneno')->setValue($userObj->getPhone());
                  if(!empty($billingObj)){
                  $fname = $billingObj->getFirstName();
                  $lname = $billingObj->getLastName();
                  $street_addr = $billingObj->getAddress();
                  $country = $billingObj->getCountry();
                  $city = $billingObj->getCity();
                  $form->get('firstname')->setValue($fname);
                  $form->get('lastname')->setValue($lname);
                  $form->get('streetaddress')->setValue($street_addr);

                  if($country != ""){
                  $form->get('country')->setValue($country);
                  }
                  if($city != ""){
                  $form->get('city')->setValue($city);
                  }
                  }
                  $eventData = array();
                  if(!empty($eventObj)){
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
                  if(!empty($eventOption)){
                  $i=0;
                  foreach($eventOption as $option){
                  $dataArr[$i++] = $option->getOption()->getId();
                  }
                  }
                  $eventData['eventOption'] = $dataArr;
                  $eventData['eventSchedule'] = $eventObj->getEventSchedule();
                  $eventData['latitude'] = $eventObj->getLatitude();
                  $eventData['longitude'] = $eventObj->getLongitude();;
                  //$eventData['eventSeat'] = $eventObj->getEventSeat();
                  $eventData['eventSeat'] = $em->getRepository('Admin\Entity\EventSeat')->findBy(array('event' => $eventObj, 'isDeleted' => 0));
                  $ticketTypeArr = array();
                  $id=0;
                  foreach($eventData['eventSeat'] as $eventSeat){
                  $ticketTypeArr[$id]['name'] = str_replace(" ","_",$eventSeat->getTicketType());
                  $ticketTypeArr[$id]['price'] = $eventSeat->getSeatPrice();
                  $ticketTypeArr[$id]['currency'] = $eventSeat->getCurrency();
                  $id++;
                  }

                  $totalQty = 0;
                  $bookedTickets = array();
                  foreach($ticketTypeArr as $ticket){
                  $totalQty += $postedData[$ticket['name']];
                  if($postedData[$ticket['name']]>0){
                  $bookedTickets[] = $ticket['name'];
                  }
                  }
                  }
                  $form->get('quantity')->setValue($totalQty); */
            } else {
                
            }
            /* return new ViewModel(array('userId'=>$userId,
              'eventData'=>$eventData,
              'checkoutContainer' => $checkoutContainer,
              'totalQty'=>$totalQty,
              'bookedTickets'=>$bookedTickets,
              'ticketTypeArr' => $ticketTypeArr,
              'form' => $form
              )); */
        }
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
                ;
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
        /* Code for Setting Layout 
          $objLayout = $em->getRepository('Admin\Entity\Layout')->findBy(array());
          $eventObj = $em->getRepository('Admin\Entity\Event')->find($eventId);
          $seatObj = $em->getRepository('Admin\Entity\EventSeat')->findBy(array('event' => $eventObj));

          $layout_id = $this->getEvent()->getRouteMatch()->getParam('layout_id') ? $this->getEvent()->getRouteMatch()->getParam('layout_id') : $eventObj->getLayout()->getId();
          if ($layout_id != '') {
          $objLayoutScreen = $em->getRepository('Admin\Entity\Layout')->find($layout_id);
          if (!empty($objLayoutScreen)) {
          $layout_screen = $objLayoutScreen->getLayoutImage();
          }
          } */
        /* Code Ends Here */
        $mainOptions = $em->getRepository('\Admin\Entity\MainOptions')->findAll();
        $eventObj = $em->getRepository('\Admin\Entity\Event')->getEvent($eventId);
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
            $eventData['event_seat'] = $em->getRepository('Admin\Entity\EventSeat')->findBy(array('event' => $eventObj, 'isDeleted' => 0));
            $layout_id = $eventObj->getLayout()->getId();
            if ($layout_id != '') {
                $objLayoutScreen = $em->getRepository('Admin\Entity\Layout')->find($layout_id);
                if (!empty($objLayoutScreen)) {
                    $eventData['layout_screen'] = $objLayoutScreen->getLayoutImage();
                }
            }
        }
        //print_r($eventObj);
        return new ViewModel(array('eventData' => $eventData, 'mainOptions' => $mainOptions, 'userId' => $userId /* 'dataLayout' => $objLayout,'layout_screen' => $layout_screen,'seat_obj' => $seatObj,'layout_id' => $layout_id,'event_id' => $eventId */));
    }

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

    public function confirmorderAction() {
        //$this->layout('layout/eventlayout');
        //$this->layout()->pageTitle = "Confirm Order";
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

                /* Checking if booked seats are available */
                $bookedTickets = array();
                $i = 0;
                foreach ($eventSeatData as $eventSeat) {
                    $ticketType = str_replace(" ", "_", $eventSeat->getTicketType());
                    if (isset($data[$ticketType])) {
                        if ($data[$ticketType] > 0) {
                            $bookedTickets[$i]['ticketId'] = $eventSeat->getId();
                            $bookedTickets[$i]['type'] = $eventSeat->getTicketType();
                            $bookedTickets[$i]['quantity'] = $data[$ticketType];
                            $bookedTickets[$i]['seats'] = $eventSeat->getNumberOfSeats();
                            $bookedTickets[$i]['price'] = $eventSeat->getSeatPrice();
                            $bookedTickets[$i]['entrance'] = $eventSeat->getSeatEntrance();
                            $bookedTickets[$i]['redeemOn'] = $eventSeat->getRedeemOn();
                            $i++;
                        }
                    }
                }

                //print_r($bookedTickets);

                if (empty($bookedTickets)) {
                    die("Some Error Occured.");
                }

                foreach ($bookedTickets as $eventSeat) {
                    $userSeatCount = $commonPlugin->getUserSeatCount($data['eventId'], $eventSeat['ticketId'], $data['eventDate'], $data['eventTime']);
                    $availableSeats = $eventSeat['seats'] - $userSeatCount;
                    if ($availableSeats < $eventSeat['quantity']) {
                        die("Tickets not available.");
                    }
                }

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

                foreach ($bookedTickets as $ticket) {
                    $qty = $ticket['quantity'];
                    for ($qty; $qty >= 1; $qty--) {
                        $noOfseatOrder = $commonPlugin->getTotalSeatCount($data['eventId'], $ticket['ticketId'], $data['eventDate'], $data['eventTime']);
                        $eventSeatObj = $em->getRepository("Admin\Entity\EventSeat")->find($ticket['ticketId']);
                        $seatNo = $noOfseatOrder + 1;
                        $seatOrderObj = new Entities\SeatOrder();
                        $seatOrderObj->setBooking($userBookingObj);
                        $seatOrderObj->setCreatedDate(date_create(date('Y-m-d H:i:s')));
                        $seatOrderObj->setEntrance($ticket['entrance']);
                        $seatOrderObj->setTicketType($ticket['type']);
                        $seatOrderObj->setRedeemOn($ticket['redeemOn']);
                        $seatOrderObj->setPrice($ticket['price']);
                        $seatOrderObj->setSeatNo($seatNo);
                        $seatOrderObj->setBarCodeNumber("");
                        $seatOrderObj->setEventSeat($eventSeatObj);
                        $em->persist($seatOrderObj);
                        $em->flush();
                    }
                }
                $schemeHost = $commonPlugin->getSchemeHostOfProj();
                $htpconfirmatinPath = $this->url()->fromRoute('htpconfirmation');
                $acceptUrl = $schemeHost . $htpconfirmatinPath;
                $conf = array();
                $securityKey = "classicinformatics123#";
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
                //echo $paramString;
                //print_r($conf);
                //die('111');
            }
            //print_r($postedData);
            //die('12121');
        }
        //echo json_encode(array());
        //die;
        // return new ViewModel(array('orderId'=>$orderId,'postedData'=>$postedData,'userObj'=>$userObj,'data'=>$data,'conf'=>$conf));
        $viewModel->setVariables(array('orderId' => $orderId, 'postedData' => $postedData, 'userObj' => $userObj, 'data' => $data, 'conf' => $conf));
        return $viewModel;
    }

    public function htpconfirmationAction() {
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
            //print_r($checkoutContainer->pdata);
            //print_r($checkoutContainer->htpresponse);
            /* Result of the alias creation:
              0=OK
              1=NOK
              2=Alias updated
              3=Cancelled by user
             */
            if ($requestData['status'] == 0 || $requestData['status'] == 3) {

                /* If Alias creation is successful then we need to send it to 2nd request */
                $user_session = new Container('user');
                $userId = $user_session->userId;
                $checkoutContainer = new Container("eventcheckout");
                $checkPageData = $checkoutContainer->pdata;
                $em = $this->getEntityManager();
                $userBookingObj = $em->getRepository('Admin\Entity\UserBooking')
                        ->findOneBy(array('user' => $userId, 'event' => $checkPageData['eventId'], 'id' => $requestData['OrderID']));
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
                    //"CVC"       => "123", /* Need to make it dynamic */
                    "DECLINEURL" => $acceptUrl,
                    "EMAIL" => $userBookingObj->getEmail(),
                    "EXCEPTIONURL" => $acceptUrl,
                    "FLAG3D" => "Y",
                    "ORDERID" => $requestData['OrderID'],
                    "PSPID" => 'testclassic',
                    "PSWD" => "test123456",
                    "USERID" => "manugarg",
                    "WIN3DS" => "MAINW",
                );
                //print_r($postData);
                $singleString = "";
                $secretKey = "classicinformatics123#";
                foreach ($postData as $key => $value) {
                    $singleString .= $key . '=' . $value . $secretKey;
                }
                //echo $singleString;
                $sha1String = sha1($singleString);
                $sha1String = strtoupper($sha1String);
                $postData["SHASIGN"] = $sha1String;
                $fields_string = '';
                foreach ($postData as $key => $value) {
                    $fields_string .= $key . '=' . $value . '&';
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
                $xml = simplexml_load_string($result, NULL, NULL);
                //echo "<pre>";
                //print_r($xml);

                if ($xml) {

                    $checkoutContainer->paymentconfirmation = array("orderID" => (string) $xml['OrderID'],
                        "PAYID" => (string) $xml['PAYID'],
                        "NCSTATUS" => (string) $xml['NCSTATUS'],
                        "NCERROR" => (string) $xml['NCERROR'],
                        "ACCEPTANCE" => (string) $xml['ACCEPTANCE'],
                        "STATUS" => (string) $xml['STATUS'],
                        "ECI" => (string) $xml['ECI'],
                        "amount" => (string) $xml['amount'],
                        "currency" => (string) $xml['currency'],
                        "PM" => (string) $xml['PM'],
                        "BRAND" => (string) $xml['BRAND'],
                        "NCERRORPLUS" => (string) $xml['NCERRORPLUS'],
                    );
                    $htmlstatus = (string) $xml['STATUS'];
                    foreach ($xml as $child => $value) {
                        if ($child == "HTML_ANSWER" && $htmlstatus == "46") {
                            //echo "In HTML_ANSWER";
                            /* If the card supports the 3D verification then this condition will run */
                            $htmlData = base64_decode($value);
                            $viewModel->setVariables(array('htmlData' => $htmlData));
                            return $viewModel;
                        } else {
                            // echo "else html";
                            return $this->redirect()->toRoute('paymentgatewayreturn');
                        }
                    }
                } else {
                    die("Some error occured at final step.");
                }
            } else {
                die("Some Error occured. Please Try again later.");
            }
            // die;
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
            $userBookingObj = $em->getRepository('Admin\Entity\UserBooking')
                    ->findOneBy(array('user' => $userId, 'event' => $checkPageData['eventId'], 'id' => $htpResponse['OrderID']));
            if (empty($userBookingObj)) {
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
                }
            } elseif ($paymentConfirmation['STATUS'] != 0 && $paymentConfirmation['STATUS'] != 2) {
                /* if Card is not 3-D means if user is not asked for password for the payment */
                $ticketPreviewPath = $this->url()->fromRoute('ticketpreview', array('bookingid' => $paymentConfirmation['orderID']));
                $redirectUrl = $schemeHost . $ticketPreviewPath;
                $userBookingObj->setPayId($paymentConfirmation['PAYID']);
                $userBookingObj->setStatus(1);
                $em->persist($userBookingObj);
                $em->flush();
            } else {
                $ticketPreviewPath = $this->url()->fromRoute('checkouterror', array('eventId' => $checkPageData['eventId']));
                $redirectUrl = $schemeHost . $ticketPreviewPath;
            }
            $viewModel->setVariables(array('redirectUrl' => $redirectUrl));
        }
        return $viewModel;
    }

}
