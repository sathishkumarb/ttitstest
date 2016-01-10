<?php

/**
 * Zend Framework (http://framework.zend.com/)
 * This class is used for Manage Event.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Admin\Form as AdminForms;
use Zend\Mvc\MvcEvent;

class EventController extends AbstractActionController {

    protected $em;
    protected $authservice;

    public function onDispatch(MvcEvent $e) {
        $admin_session = new Container('admin');
        $username = $admin_session->username;
        if (empty($username)) {
            /* if not logged in redirect the user to login page */
            return $this->redirect()->toRoute('adminlogin');
        }
        /* Set Default layout for all the actions */
        $this->layout('layout/adminlayout');
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
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }

        return $this->authservice;
    }

    public function indexAction() {
        return new ViewModel();
    }

    //Added by Yesh 20112015
    /**
     * insert seats based on zone
     * @param type $zoneSeats
     * @param type $eventId
     * @param type $zoneId
     */
    private function insertZoneSeats($zoneSeats, $eventId, $zoneId) {
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $scheduleData = $em->getRepository('Admin\Entity\EventSchedule')->getSchedulesByEventID($eventId);
        foreach ($scheduleData as $data) {
            $scheduleId = $data["id"];
            if (!empty($zoneSeats)) {
                $seatPlan = $zoneSeats->seatPlan;
                try {
                    for ($n = 0; $n < count($seatPlan); $n++) {
                        $match = [];
                        // Save Zone Seats
                        $seatLabel = $seatPlan[$n]->seatID;
                        $zoneSeatsObj = new Entities\ZoneSeats();
                        $zoneSeatsObj->setScheduleId($scheduleId);
                        $zoneSeatsObj->setEventId($eventId);
                        $zoneSeatsObj->setZoneId($zoneId);
                        $zoneSeatsObj->setSeatLabel($seatLabel);
                        //make column and row
                        $label = explode("_", $seatLabel)[1];
                        preg_match_all('/^([^\d]+)(\d+)/', $label, $match);
                        $rowId = $match[1][0];
                        $colId = $match[2][0];
                        $zoneSeatsObj->setRowId($rowId);
                        $zoneSeatsObj->setColId($colId);
                        $zoneSeatsObj->setSeatAvailability(0);
                        $zoneSeatsObj->setBookingId(0);
                        $zoneSeatsObj->setUserId(0);
                        $em->persist($zoneSeatsObj);
                        $em->flush();
                    }
                } catch (Exception $ex) {
                    echo "Caught exception: " . get_class($ex) . "\n";
                    echo "Message: Event Event Map" . $ex->getMessage() . "\n";
                    die();
                }
            }
        }
    }

    /**
     * ajax process upload Action
     */
    public function ajaxprocessuploadAction() {
        if (isset($_FILES["FileInput"]) && $_FILES["FileInput"]["error"] == UPLOAD_ERR_OK) {
            ############ Edit settings ##############
            $UploadDirectory = getcwd() . '/public/uploads/maps/'; //specify upload directory ends with / (slash)
            ##########################################
            /*
              Note : You will run into errors or blank page if "memory_limit" or "upload_max_filesize" is set to low in "php.ini".
              Open "php.ini" file, and search for "memory_limit" or "upload_max_filesize" limit
              and set them adequately, also check "post_max_size".
             */
            //check if this is an ajax request
            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                die();
            }
            //Is file size is less than allowed size.
            if ($_FILES["FileInput"]["size"] > 5242880) {
                die("File size is too big!");
            }
            //allowed file type Server side check
            switch (strtolower($_FILES['FileInput']['type'])) {
                //allowed file types
                case 'image/png':
                case 'image/jpeg':
                case 'image/pjpeg':
                    break;
                default:
                    die('Unsupported File!'); //output error
            }
            $File_Name = strtolower($_FILES['FileInput']['name']);
            $File_Ext = substr($File_Name, strrpos($File_Name, '.')); //get file extention
            $Random_Number = rand(0, 9999999999); //Random number to be added to name.
            $NewFileName = $Random_Number . $File_Ext; //new file name
            if (move_uploaded_file($_FILES['FileInput']['tmp_name'], $UploadDirectory . $NewFileName)) {
                echo json_encode(array('status' => TRUE, 'msg' => 'Success! File Uploaded.', 'filename' => $NewFileName));
                die();
            } else {
                echo json_encode(array('status' => FALSE, 'msg' => 'error uploading File!'));
                die();
            }
        } else {
            die('Something wrong with upload! Is "upload_max_filesize" set correctly?');
        }
    }

    /**
     * ajax save drawing Action
     */
    public function ajaxsavedrawingAction() {
        $mapId = ''; //added event map id
        $zoneId = ''; //added zone id
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        if ($request->isPost()) {
            $data = $request->getPost();
            $eventId = $data['eventID'];
            $layoutID = $data['layoutID'];
            if ($layoutID === "") {
                $em->getRepository('Admin\Entity\EventMap')->deleteAllExistsData($eventId);
                $em->getRepository('Admin\Entity\ZoneSeats')->deleteAllExistsData($eventId);
                $em->getRepository('Admin\Entity\MapZone')->deleteAllExistsData($eventId);
            }
            $str = $data['jsonData'];
            $decode = json_decode($str);
            $mapObject = serialize($decode);
            $areas = $decode->areas;
            try {
                // Save Event Map
                $eventMapObj = new Entities\EventMap();
                $eventMapObj->setEventId($eventId);
                $eventMapObj->setMapObject($mapObject);
                $em->persist($eventMapObj);
                $em->flush();
                $mapId = $eventMapObj->getId();
            } catch (Exception $ex) {
                echo "Caught exception: " . get_class($ex) . "\n";
                echo "Message: Event Event Map" . $ex->getMessage() . "\n";
                die();
            }
            for ($i = 0; $i < count($areas); $i++) {
                $zoneTitle = $areas[$i]->zoneTitle;
                $zoneDtcm = $areas[$i]->zoneDtcm;
                $zonePrice = $areas[$i]->zonePrice;
                $zoneCount = $areas[$i]->zoneCount;
                $zoneSeats = $areas[$i]->zoneSeats;
                try {
                    //Save map zone
                    $mapZoneObj = new Entities\MapZone();
                    $mapZoneObj->setEventId($eventId);
                    $mapZoneObj->setMapId($mapId);
                    $mapZoneObj->setZoneTitle($zoneTitle);
                    $mapZoneObj->setZoneDtcm($zoneDtcm);
                    $mapZoneObj->setZonePrice($zonePrice);
                    $mapZoneObj->setZoneCount($zoneCount);
                    $em->persist($mapZoneObj);
                    $em->flush();
                    $zoneId = $mapZoneObj->getId();
                } catch (Exception $ex) {
                    echo "Caught exception: " . get_class($ex) . "\n";
                    echo "Message: Event Event Map" . $ex->getMessage() . "\n";
                    die();
                }
                $this->insertZoneSeats($zoneSeats, $eventId, $zoneId);
            }
            echo 'success';
            die();
        }
        echo 'fail';
        die();
    }

    /**
     * ajax edit drawing Action
     */
    public function ajaxeditdrawingAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $id;
        $eventId;
        $mapObject;
        if ($request->isPost()) {
            $data = $request->getPost();
            $eventId = $data['eventID'];
            //$layoutID = $data['layoutID']; //not in used, may be future
            $eventMapObj = $em->getRepository('Admin\Entity\EventMap')->getMapByEventId($eventId);
            foreach ($eventMapObj as $obj) {
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

    /**
     * event reports Action
     * @return ViewModel 
     */
    public function eventreportsAction() {
        $this->layout()->pageTitle = 'Reports'; /* Setting page title */
        return new ViewModel();
    }

    /**
     * ajax get event list Action
     */
    public function ajaxgeteventlistAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        if ($request->isGet()) {
            $eventObj = $em->getRepository('Admin\Entity\Event')->getEventList();
            print_r($eventObj);
            exit;
            print json_encode(array('status' => 'success', 'list' => $eventObj));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }
    /**
     * build Report HTML Output Action
     * @param type $eventlist
     * @return string
     */
    public function buildReportHTMLOutputAction($eventlist) {
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $eventObj = $em->getRepository('Admin\Entity\Event')->getEvent($eventlist);
        $userBookingObj = $em->getRepository('Admin\Entity\UserBooking')->findBy(array('event' => $eventlist, 'status' => 1));
        $html = '<div class="row section-title">
            <div class="col-md-6">
            <h4>' . $eventObj->getEventName() . '<br><small>' . $eventObj->getEventDesc() . '</small></h4>
                </div>
                <div class="col-md-6">
                <button id="printThis" class="btn btn-primary rgtbn"><!--i class="icon-plus"></i--> Print</button>
                </div>
                </div>';
        $html .= '<div class="row">
            <div class="table-responsive">
            <table class="table table-striped">
            <thead>
            <tr>
            <th>Purchased DateTime</th>
            <th>Ticket#</th>
            <th>Payment Type</th>
            <th>Amount</th>
            <th>First Name</th>
            <th>Last name</th>
            <th>Quantity</th>
            <th>Email</th>
            <th>Zip code</th>
            <th>Contact No:</th>
            <th>Show date</th>
            <th>Zone</th>
            <th>Seat</th>
            </tr>
            </thead>
            <tbody>';
        foreach ($userBookingObj as $row) {
            $html .= '<tr>';
            $html .= '<td>' . $row->getBookingMadeDate()->format('d/m/Y') . '</td>';
            $html .= '<td>Ticket#</td>';
            $html .= '<td>' . $row->getCardType() . '</td>';
            $html .= '<td>' . $row->getBookingTotalPrice() . '</td>';
            $html .= '<td>' . $row->getFirstName() . '</td>';
            $html .= '<td>' . $row->getLastName() . '</td>';
            $html .= '<td>' . $row->getBookingSeatCount() . '</td>';
            $html .= '<td>' . $row->getEmail() . '</td>';
            $html .= '<td>Zip code</td>';
            $html .= '<td>' . $row->getPhoneNo() . '</td>';
            $html .= '<td>' . $row->getEventDate()->format('d/m/Y') . '</td>';
            $html .= '<td>Zone</td>';
            $html .= '<td>Seat</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div></div>';
        return $html;
    }
    
    /**
     * ajax get event report Action
     */
    public function ajaxgeteventreportAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $result = '';
        if ($request->isPost()) {
            $data = $request->getPost();
            $reporttype = $data['reporttype'];
            switch ($reporttype) {
                case 'fnsreport':
                    break;
                case 'eventlist':
                    $eventlist = $data['eventlist'];
                    $result = $this->buildReportHTMLOutputAction($eventlist);
                    break;
                case 'startend':
                    $startdate = $data['startdate'];
                    $enddate = $data['enddate'];
                    break;
            }
            print json_encode(array('status' => 'success', 'result' => $result));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }
    //Added by Yesh 20112015

    /**
     * This function is used for add event. 
     * @Author Vinod Kandwal
     */
    public function addAction() {
        $this->layout()->pageTitle = 'Add Event'; /* Setting page title */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        /* Check with passed password exist in DB */
        $objCategories = $em->getRepository('Admin\Entity\Categories')->findBy(array('status' => 1));
        $objCountries = $em->getRepository('Admin\Entity\Countries')->findBy(array('countryExist' => 1));
        $objLayout = $em->getRepository('Admin\Entity\Layout')->findBy(array());
        $objOption = $em->getRepository('Admin\Entity\MainOptions')->findBy(array());

        $layout_id = $this->getEvent()->getRouteMatch()->getParam('layout_id') ? $this->getEvent()->getRouteMatch()->getParam('layout_id') : 1;

        $dataEvent = array('event_image_big' => '', 'event_image_medium' => '', 'event_image_small' => '', 'event_image_banner' => '');
        $request = $this->getRequest();  /* Fetching Request */
        if ($request->isPost()) {
            $data = $request->getPost();
            $txtlayout = $data['txtlayout'];
            $txttitle = $data['txttitle'];
            $txtperfcode = $data['txtperfcode'];
            $txtdesc = $data['txtdesc'];
            $txtartist = $data['txtartist'];
            $txtaddress = $data['txtaddress'];
            $txtcountry = $data['txtcountry'];
            $txtcity = $data['txtcity'];
            $txtzipcode = $data['txtzipcode'];
            $txtvenue_title = $data['txtvenue_title'];
            $txtlink = $data['txtlink'];
            $txtdate = $data['txtdate'];
            $txttime = $data['txttime'];

            $txtcategory = $data['txtcategory'];
            $txt_check_feature = $data['txt_check_feature'];
            $txtimage_big = $data['txtimage_big'];
            $txtimage_medium = $data['txtimage_medium'];
            $txtimage_small = $data['txtimage_small'];
            $txtimage_banner = $data['txtimage_banner'];
            $txtoption = $data['txtoption'];

            $datetimeCountHiddenValue = $data['datetimeCountHiddenValue'];

            $uploadsDir = getcwd() . '/public/uploads';
            if (!file_exists($uploadsDir)) {
                mkdir(($uploadsDir), 0777, true);
            }
            $uploadsDirPath = getcwd() . '/public/uploads/event/';
            if (!file_exists($uploadsDirPath)) {
                mkdir(($uploadsDirPath), 0777, true);
            }

            try {
                $fileName = '';
                $upload = new \Zend\File\Transfer\Adapter\Http();
                $upload->setDestination($uploadsDirPath);
                $files = $upload->getFileInfo();
                // $upload->addValidator('FilesSize', false, array('min' => '10kB', 'max' => '4MB'));
                // $upload->addValidator('Extension', false, array('jpg', 'png','jpeg','gif'));
                // echo '<pre>'; print_r($files); die;
                if (count($files) > 0) {
                    foreach ($files as $file => $info) {
                        $fileName = uniqid() . date("ymd_his") . '_' . $info ['name'];
                        if ($file == 'txtimage_big') {
                            $dataEvent['event_image_big'] = $fileName;
                        }
                        if ($file == 'txtimage_medium') {
                            $dataEvent['event_image_medium'] = $fileName;
                        }
                        if ($file == 'txtimage_small') {
                            $dataEvent['event_image_small'] = $fileName;
                        }
                        if ($file == 'txtimage_banner') {
                            $dataEvent['event_image_banner'] = $fileName;
                        }
                        if ($file == 'txtvenue_icon') {
                            $fileName = 'v_' . uniqid() . date("his") . '_' . $info ['name'];
                            $dataEvent['event_venue_icon'] = $fileName;
                        }
                        $upload->addFilter('Rename', array(
                            'target' => $uploadsDirPath . $fileName,
                            'overwrite' => true
                        ));
                        if ($upload->isValid($file)) {
                            $upload->receive($file);
                        }
                    }
                }
            } catch (Zend_File_Transfer_Exception $e) {
                echo $e->getMessage();
                exit();
            }
            try {
                $currentDate = date_create(date('Y-m-d H:i:s'));
                $objLayoutId = $em->getRepository('Admin\Entity\Layout')->find($txtlayout);
                $objCategoriesId = $em->getRepository('Admin\Entity\Categories')->find($txtcategory);
                $objCountryId = $em->getRepository('Admin\Entity\Countries')->find($txtcountry);
                $objCityId = $em->getRepository('Admin\Entity\City')->find($txtcity);

                $latitude = $longitude = '';
                //$address = $txtaddress . ", " . $txtcity . ", " . $txtcountry . ", " . $txtzipcode;
                $address = $txtaddress . ", " . $txtzipcode . ", " . $objCityId->getCityName() . ", " . $objCountryId->getCountryName();
                $address = urlencode($address);
                $request_url = "http://maps.googleapis.com/maps/api/geocode/xml?address=" . $address . "&sensor=true";

                $xml = simplexml_load_file($request_url);  // or die("url not loading");
                if (!empty($xml)) {
                    $status = $xml->status;
                    if ($status == "OK") {
                        $latitude = $xml->result->geometry->location->lat;
                        $longitude = $xml->result->geometry->location->lng;
                    }
                }

                // Save Event details
                $eventObj = new Entities\Event();
                $eventObj->setEventName($txttitle);
                $eventObj->setPerfCode($txtperfcode); //Added by Yesh - 2015-12-31
                $eventObj->setEventDesc($txtdesc);
                $eventObj->setEventArtist($txtartist);
                $eventObj->setEventCountry($objCountryId);
                $eventObj->setEventCity($objCityId);
                $eventObj->setEventAddress($txtaddress);
                $eventObj->setEventZip($txtzipcode);
                $eventObj->setEventVenueTitle($txtvenue_title);
                $eventObj->setEventVenueIcon($dataEvent['event_venue_icon']);
                $eventObj->setEventImageBig($dataEvent['event_image_big']);
                $eventObj->setEventImageMedium($dataEvent['event_image_medium']);
                $eventObj->setEventImageSmall($dataEvent['event_image_small']);
                $eventObj->setEventImageBanner($dataEvent['event_image_banner']);
                $eventObj->setEventLink($txtlink);
                $eventObj->setLongitude($longitude);
                $eventObj->setLatitude($latitude);
                $eventObj->setLayout($objLayoutId);
                $eventObj->setCategory($objCategoriesId);
                $eventObj->setFeatured($txt_check_feature);
                $eventObj->setStatus(0);
                $eventObj->setCreatedDate($currentDate);
                $em->persist($eventObj);
                $em->flush();
            } catch (Zend_Exception $e) {
                echo "Caught exception: " . get_class($e) . "\n";
                echo "Message: Event Entity" . $e->getMessage() . "\n";
                exit();
            }
            try {
                // Save Event allow options
                if (count($txtoption) > 0) {
                    foreach ($txtoption as $optionId) {
                        $objOptionId = $em->getRepository('Admin\Entity\MainOptions')->find($optionId);
                        $eventOptionObj = new Entities\EventOption();
                        $eventOptionObj->setEvent($eventObj);
                        $eventOptionObj->setOption($objOptionId);
                        $eventOptionObj->setCreatedDate($currentDate);
                        $eventOptionObj->setIsDeleted(0);
                        $em->persist($eventOptionObj);
                        $em->flush();
                    }
                }
            } catch (Zend_Exception $e) {
                echo "Caught exception: " . get_class($e) . "\n";
                echo "Message: Event Option Entity" . $e->getMessage() . "\n";
                exit();
            }
            try {
                // Save Event Scheduling
                for ($i = 1; $i <= $datetimeCountHiddenValue; $i++) {
                    $arrdate = explode('/', $data['txtdate' . $i]);
                    $eventdate = date_create($arrdate[2] . '-' . $arrdate[0] . '-' . $arrdate[1]);
                    $event_time = date_create(date('H:i:s', strtotime($data['txttime' . $i])));
                    $eventScheduleObj = new Entities\EventSchedule();
                    $eventScheduleObj->setEvent($eventObj);
                    $eventScheduleObj->setEventDate($eventdate);
                    $eventScheduleObj->setEventTime($event_time);
                    $eventScheduleObj->setCreatedDate($currentDate);
                    $eventScheduleObj->setIsDeleted(0);
                    $em->persist($eventScheduleObj);
                    $em->flush();
                }
                $eventId = $eventObj->getId();
                $layoutId = $objLayoutId->getId();
                return $this->redirect()->toRoute('layout', array('event_id' => $eventId, 'layout_id' => $layoutId));
            } catch (Zend_Exception $e) {
                echo "Caught exception: " . get_class($e) . "\n";
                echo "Message: Event Schedule Entity" . $e->getMessage() . "\n";
                exit();
            }
        }

        return new ViewModel(array(
            'dataCategories' => $objCategories,
            'dataCountries' => $objCountries,
            'dataLayout' => $objLayout,
            'dataOption' => $objOption,
            'layout_id' => $layout_id,
        ));
    }

    /**
     * Edit by Yesh
     * @return ViewModel
     */
    public function layoutAction() {
        $this->layout('layout/layoutscreen');
        $this->layout()->pageTitle = 'Layout'; /* Setting page title */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $event_id = $this->getEvent()->getRouteMatch()->getParam('event_id') ? $this->getEvent()->getRouteMatch()->getParam('event_id') : '';
        $layout_id = $this->getEvent()->getRouteMatch()->getParam('layout_id') ? $this->getEvent()->getRouteMatch()->getParam('layout_id') : '';
        $map_count = $em->getRepository('Admin\Entity\EventMap')->getCountByEventId($event_id);
        $eventObj = $em->getRepository('Admin\Entity\Event')->find($event_id);
        $eventData = array();
//        echo '<pre>';
//        print_r($eventObj);
//        echo '</pre>';
        return new ViewModel(array(
            'eventData' => $eventData,
            'map_count' => $map_count,
            'layout_id' => $layout_id,
            'event_id' => $event_id
        ));
    }

    /**
     * This function is used for edit event. 
     * @Author Vinod Kandwal
     */
    public function editAction() {
        $this->layout()->pageTitle = 'Edit Event'; /* Setting page title */
        $eventId = $this->params('eventId');
        $layout_id = $this->getEvent()->getRouteMatch()->getParam('layout_id') ? $this->getEvent()->getRouteMatch()->getParam('layout_id') : '';
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $objCategories = $em->getRepository('Admin\Entity\Categories')->findBy(array('status' => 1));
        $objCountries = $em->getRepository('Admin\Entity\Countries')->findBy(array('countryExist' => 1));
        $objLayout = $em->getRepository('Admin\Entity\Layout')->findBy(array());
        $objOption = $em->getRepository('Admin\Entity\MainOptions')->findBy(array());
        $eventData = $em->getRepository('Admin\Entity\Event')->find($eventId);
        if (empty($eventData)) {
            $msg = "Event donot exists";
            $flashMessenger->addMessage($msg);
            return $this->redirect()->toRoute('listevent');
        }
        $eventOptionData = $em->getRepository('Admin\Entity\EventOption')->findBy(array('event' => $eventId, 'isDeleted' => 0));
        $eventScheduleData = $em->getRepository('Admin\Entity\EventSchedule')->findBy(array('event' => $eventId, 'isDeleted' => 0));
        $eventopt = array();
        $eventsch = array();
        foreach ($eventOptionData as $eventObj) {
            $eventopt[] = $eventObj->getOption()->getId();
        }
        $i = 0;
        foreach ($eventScheduleData as $schedule) {
            $eventsch[$i]['eventdate'] = $schedule->getEventDate();
            $eventsch[$i]['eventtime'] = $schedule->getEventTime();
            $eventsch[$i]['scheduleid'] = $schedule->getId();
            ++$i;
        }
        $perfcode = $eventData->getPerfCode();
        $title = $eventData->getEventName();
        $desc = $eventData->getEventDesc();
        $artist = $eventData->getEventArtist();
        $address = $eventData->getEventAddress();
        $country = $eventData->getEventCountry()->getId();
        $city = $eventData->getEventCity()->getId();
        $venuetitle = $eventData->getEventVenueTitle();
        $event_link = $eventData->getEventLink();
        $featured = $eventData->getFeatured();
        $zip = $eventData->getEventZip();
        $eventvenueicon = $eventData->getEventVenueIcon();
        $eventBigImg = $eventData->getEventImageBig();
        $eventMedImg = $eventData->getEventImageMedium();
        $eventSmallImg = $eventData->getEventImageSmall();
        $catid = $eventData->getCategory()->getId();
        $layoutid = $eventData->getLayout()->getId();
        if ($featured == 1) {
            $featured_str = 'checked="checked"';
            $img_banner_str = $eventData->getEventImageBanner();
        } else {
            $featured_str = '';
            $img_banner_str = "";
        }
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $objCity = $em->getRepository('Admin\Entity\City')->findBy(array('countryId' => $country));
        $formdata = array(
            'perfcode' => $perfcode,
            'title' => $title,
            'description' => $desc,
            'artist' => $artist,
            'country' => $country,
            'city' => trim($city),
            'address' => $address,
            'zip' => $zip,
            'venuetitle' => $venuetitle,
            'eventlink' => $event_link,
            'featured' => $featured_str,
            'img_banner_str' => $img_banner_str,
            'eventvenueicon' => $eventvenueicon,
            'eventbigicon' => $eventBigImg,
            'eventmedicon' => $eventMedImg,
            'eventsmallicon' => $eventSmallImg,
            'category' => $catid,
            'activelayout' => $layoutid,
            'activeoptions' => $eventopt,
            'evntschedule' => $eventsch,
            'eventid' => $eventId
        );
        $request = $this->getRequest();  /* Fetching Request */
        if ($request->isPost()) {
            $data = $request->getPost();
            $txtperfcode = $data['txtperfcode'];
            $txtlayout = $data['txtlayout'];
            $txttitle = $data['txttitle'];
            $txtdesc = $data['txtdesc'];
            $txtartist = $data['txtartist'];
            $txtaddress = $data['txtaddress'];
            $txtcountry = $data['txtcountry'];
            $txtcity = $data['txtcity'];
            $txtzipcode = $data['txtzipcode'];
            $txtvenue_title = $data['txtvenue_title'];
            $txtlink = $data['txtlink'];
            $txtdate = $data['txtdate1'];
            $txttime = $data['txttime'];
            $txtcategory = $data['txtcategory'];
            $txt_check_feature = $data['txt_check_feature'];
            $txtimage_big = $data['txtimage_big'];
            $txtimage_medium = $data['txtimage_medium'];
            $txtimage_small = $data['txtimage_small'];
            $txtimage_banner = $data['txtimage_banner'];
            $txtoption = $data['txtoption'];
            $datetimeCountHiddenValue = $data['datetimeCountHiddenValue'];
            $uploadsDir = getcwd() . '/public/uploads';
            if (!file_exists($uploadsDir)) {
                mkdir(($uploadsDir), 0777, true);
            }
            $uploadsDirPath = getcwd() . '/public/uploads/event/';
            if (!file_exists($uploadsDirPath)) {
                mkdir(($uploadsDirPath), 0777, true);
            }
            try {
                $fileName = '';
                $upload = new \Zend\File\Transfer\Adapter\Http();
                $upload->setDestination($uploadsDirPath);
                $files = $upload->getFileInfo();
                // $upload->addValidator('FilesSize', false, array('min' => '10kB', 'max' => '4MB'));
                // $upload->addValidator('Extension', false, array('jpg', 'png','jpeg','gif'));	
                // echo '<pre>'; print_r($files); die;
                if (count($files) > 0) {
                    foreach ($files as $file => $info) {
                        //$fileName = uniqid() . date("ymd_his") . '_' . $info ['name'];
                        if ($file == 'txtimage_big') {
                            $fileName = $dataEvent['txtimage_big'] = 'b_' . uniqid() . date("his") . '_' . $info ['name'];
                        }
                        if ($file == 'txtimage_medium') {
                            $fileName = $dataEvent['event_image_medium'] = 'm_' . uniqid() . date("his") . '_' . $info ['name'];
                        }
                        if ($file == 'txtimage_small') {
                            $fileName = $dataEvent['event_image_small'] = 's_' . uniqid() . date("his") . '_' . $info ['name'];
                        }
                        if ($file == 'txtimage_banner') {
                            $fileName = $dataEvent['event_image_banner'] = 'f_' . uniqid() . date("his") . '_' . $info ['name'];
                        }
                        if ($file == 'txtvenue_icon') {
                            $fileName = 'v_' . uniqid() . date("his") . '_' . $info ['name'];
                            $dataEvent['event_venue_icon'] = $fileName;
                        }
                        $upload->addFilter('Rename', array(
                            'target' => $uploadsDirPath . $fileName,
                            'overwrite' => true
                        ));
                        if ($upload->isValid($file)) {
                            $upload->receive($file);
                        }
                    }
                }
            } catch (Zend_File_Transfer_Exception $e) {
                echo $e->getMessage();
                exit();
            }
            try {
                $currentDate = date_create(date('Y-m-d H:i:s'));
                $objLayoutId = $em->getRepository('Admin\Entity\Layout')->find($txtlayout);
                $objCategoriesId = $em->getRepository('Admin\Entity\Categories')->find($txtcategory);
                $objCountryId = $em->getRepository('Admin\Entity\Countries')->find($txtcountry);
                $objCityId = $em->getRepository('Admin\Entity\City')->find($txtcity);

                $latitude = $longitude = '';
                //$address = $txtaddress . ", " . $txtcity . ", " . $txtcountry . ", " . $txtzipcode;
                $address = $txtaddress . ", " . $txtzipcode . ", " . $objCityId->getCityName() . ", " . $objCountryId->getCountryName();
                $address = urlencode($address);
                $request_url = "http://maps.googleapis.com/maps/api/geocode/xml?address=" . $address . "&sensor=true";

                $xml = simplexml_load_file($request_url);  // or die("url not loading");
                if (!empty($xml)) {
                    $status = $xml->status;
                    if ($status == "OK") {
                        $latitude = $xml->result->geometry->location->lat;
                        $longitude = $xml->result->geometry->location->lng;
                    }
                }
                // Save Event details
                //$eventData = new Entities\Event();
                $eventData->setPerfCode($txtperfcode);
                $eventData->setEventName($txttitle);
                $eventData->setEventZip($txtzipcode);
                $eventData->setEventDesc($txtdesc);
                $eventData->setEventArtist($txtartist);
                $eventData->setEventCountry($objCountryId);
                $eventData->setEventCity($objCityId);
                $eventData->setEventAddress($txtaddress);
                $eventData->setEventVenueTitle($txtvenue_title);
                if ($_FILES['txtvenue_icon']['name'] != '') {
                    $eventData->setEventVenueIcon($dataEvent['event_venue_icon']);
                }
                if ($_FILES['txtimage_big']['name'] != '') {
                    $eventData->setEventImageBig($dataEvent['event_image_big']);
                }
                if ($_FILES['txtimage_medium']['name'] != '') {
                    $eventData->setEventImageMedium($dataEvent['event_image_medium']);
                }
                if ($_FILES['txtimage_small']['name'] != '') {
                    $eventData->setEventImageSmall($dataEvent['event_image_small']);
                }
                if ($_FILES['txtimage_banner']['name'] != '') {
                    $eventData->setEventImageBanner($dataEvent['event_image_banner']);
                }
                $eventData->setEventLink($txtlink);
                $eventData->setLayout($objLayoutId);
                $eventData->setCategory($objCategoriesId);
                $eventData->setFeatured($txt_check_feature);
                $eventData->setStatus(1);
                $eventData->setLongitude($longitude);
                $eventData->setLatitude($latitude);
                $eventData->setModifiedDate($currentDate);
                $em->persist($eventData);
                $em->flush();
            } catch (Zend_Exception $e) {
                echo "Caught exception: " . get_class($e) . "\n";
                echo "Message: Event Entity" . $e->getMessage() . "\n";
                exit();
            }
            try {
                foreach ($eventOptionData as $eventObj) {
                    $eventObj->setIsDeleted(1);
                    $em->persist($eventObj);
                    $em->flush();
                }

                //$em->remove($eventOptionData);
                // Save Event allow options                
                if (count($txtoption) > 0) {
                    foreach ($txtoption as $optionId) {
                        //echo "Option id is " . $optionId;
                        $objOptionId = $em->getRepository('Admin\Entity\MainOptions')->find($optionId);
                        $eventOptionObj = new Entities\EventOption();
                        $eventOptionObj->setEvent($eventData);
                        $eventOptionObj->setOption($objOptionId);
                        $eventOptionObj->setCreatedDate($currentDate);
                        $eventOptionObj->setIsDeleted(0);
                        $em->persist($eventOptionObj);
                        $em->flush();
                    }
                }
            } catch (Zend_Exception $e) {
                echo "Caught exception: " . get_class($e) . "\n";
                echo "Message: Event Option Entity" . $e->getMessage() . "\n";
                exit();
            }

            try {
                if ($data['txtdate1'] != "") {
                    // Save Event Scheduling
                    for ($i = 1; $i <= $datetimeCountHiddenValue; $i++) {
                        $arrdate = explode('/', $data['txtdate' . $i]);
                        $eventdate = date_create($arrdate[2] . '-' . $arrdate[0] . '-' . $arrdate[1]);
                        $event_time = date_create(date('H:i:s', strtotime($data['txttime' . $i])));
                        $eventScheduleObj = new Entities\EventSchedule();
                        $eventScheduleObj->setEvent($eventData);
                        $eventScheduleObj->setEventDate($eventdate);
                        $eventScheduleObj->setEventTime($event_time);
                        $eventScheduleObj->setCreatedDate($currentDate);
                        $eventScheduleObj->setIsDeleted(0);
                        $em->persist($eventScheduleObj);
                        $em->flush();
                    }
                }
                $flashMessenger = $this->flashMessenger();
                $flashMessenger->setNamespace('success');
                $msg = "Event has been updated successfully";
                $flashMessenger->addMessage($msg);
                $eventId = $eventData->getId();
                $layoutId = $objLayoutId->getId();
                return $this->redirect()->toRoute('listevent');
            } catch (Zend_Exception $e) {
                echo "Caught exception: " . get_class($e) . "\n";
                echo "Message: Event Schedule Entity" . $e->getMessage() . "\n";
                exit();
            }
        }
        //return new ViewModel();
        return new ViewModel(array(
            'dataCategories' => $objCategories,
            'dataCountries' => $objCountries,
            'dataCities' => $objCity,
            'dataLayout' => $objLayout,
            'dataOption' => $objOption,
            'formdata' => $formdata,
            'layout_id' => $layout_id
        ));
    }

    /**
     * This function is used for list of event. 
     * @Author Vinod Kandwal
     */
    public function listAction() {
        $this->layout('layout/adminlayout');
        $this->layout()->pageTitle = 'List Events'; /* Setting page title */
        return new ViewModel();
    }

    /**
     * This function is used for ajaxeventslist - Event Data send when Datatable made AJAX request
     * @return json -Json Data for Ajax Request
     * @author Manu Garg
     */
    public function ajaxeventslistAction() {
        $em = $this->getEntityManager();

        $request = $this->getRequest();
        $sqlArr['searchKey'] = $request->getQuery('sSearch');
        $sqlArr['sortcolumn'] = $request->getQuery('iSortCol_0');
        $sqlArr['sorttype'] = $request->getQuery('sSortDir_0');    // desc or asc 
        $sqlArr['iDisplayStart'] = $request->getQuery('iDisplayStart');  // offset
        $sqlArr['sEcho'] = $request->getQuery('sEcho');
        $sqlArr['limit'] = $request->getQuery('iDisplayLength');
        $userData = $em->getRepository('\Admin\Entity\Event')->getEventsListingAtAdminForDataTable($sqlArr);
        echo json_encode($userData);
        exit();
    }

    /**
     * This function is used for event change status - AJAX call to change the status of an Event 
     * @return string 1-success
     * @author Manu Garg
     */
    public function eventchangestatusAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */

        $changeType = $this->params('type');
        $eventId = $this->params('eventId');

        if (!empty($changeType) && !empty($eventId)) {
            $eventObj = $em->getRepository('\Admin\Entity\Event')->find($eventId);
            if (!empty($eventObj)) {
                if ($changeType == "active") {
                    $eventObj->setStatus('1');
                } else {
                    $eventObj->setStatus('0');
                }
                $eventObj->setModifiedDate(date_create(date('Y-m-d H:i:s')));
                $em->persist($eventObj);
                $em->flush();
                echo "1";
                exit;
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    /**
     * This function is used for event delete - AJAX call to delete the event
     * @return string 1-success
     * @author Manu Garg
     */
    public function eventdeleteAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */

        $eventId = $this->params('eventId');
        if (empty($eventId)) {
            echo "2";
        } else {
            $eventObj = $em->getRepository('\Admin\Entity\Event')->find($eventId);
            if (!empty($eventObj)) {
                $eventObj->setStatus(2);
                $eventObj->setModifiedDate(date_create(date('Y-m-d H:i:s')));
                $em->persist($eventObj);
                $em->flush();
                echo "1";
            } else {
                echo "3";
            }
        }
        exit();
    }

    /**
     * This function is used for event Cancel - AJAX call to cancel the event
     * @return string 1-success
     * @author Manu Garg
     */
    public function eventcancelAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */

        $eventId = $this->params('eventId');
        if (empty($eventId)) {
            echo "2";
        } else {
            $eventObj = $em->getRepository('\Admin\Entity\Event')->find($eventId);
            if (!empty($eventObj)) {
                $eventObj->setStatus(3);
                $eventObj->setModifiedDate(date_create(date('Y-m-d H:i:s')));
                $em->persist($eventObj);
                $em->flush();
                echo "1";
            } else {
                echo "3";
            }
        }
        exit();
    }

    public function eventscheduledeleteAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $eventScheduleId = $this->params('eventscheduleid');
        if (empty($eventScheduleId)) {
            echo "2";
        } else {
            $eventScheduleObj = $em->getRepository('\Admin\Entity\EventSchedule')->find($eventScheduleId);
            if (!empty($eventScheduleObj)) {
                $eventScheduleObj->setIsDeleted(1);
                $em->persist($eventScheduleObj);
                $em->flush();
                echo "1";
            } else {
                echo "3";
            }
        }
        die;
    }

    public function layoutnewAction() {
        return new ViewModel(array(
        ));
    }

}
