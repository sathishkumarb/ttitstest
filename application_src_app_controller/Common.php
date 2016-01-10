<?php

/**
 *  Plugin - for common things 
 * 
 */

namespace Application\Controller\Plugin;

//use Zend\Mvc\Controller\AbstractActionController;
//use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Application\Form as Forms;
use Zend\Mail as Mail;
use Zend\Mime as Mime;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class Common extends AbstractPlugin {

    protected $em;
    protected $authservice;

    public function setEntityManager(EntityManager $em) {
        $this->em = $em;
    }

    public function getEntityManager() {
        if (null === $this->em) {
            $this->em = $this->getController()->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }

    public function getAuthService() {
        if (!$this->authservice) {
            $this->authservice = $this->getController()->getServiceLocator()
                    ->get('AuthService');
        }
        return $this->authservice;
    }

    /**
     * sendEmail
     * @param mixed $tags -Array of the Macros need to be replaced defined in Email template
     * @param string $emailKey -unique email key to fetch the email template
     * @param integer $userId -UserId of the user to which the email to be sent. 
     *                         default is 0 for admin
     * @return integer 1-success  2-error
     * @author Manu Garg
     */
    public function sendEmail($tags, $emailKey, $userId = 0, $filename = "") {
        $em = $this->getEntityManager();
        $adminemailObj = $em->getRepository('Admin\Entity\Settings')->findOneBy(array('metaKey' => 'admin_email'));
        $supportemailObj = $em->getRepository('Admin\Entity\Settings')->findOneBy(array('metaKey' => 'admin_support_email'));

        $adminemail = $adminemailObj->getMetaValue();
        $supportemail = $supportemailObj->getMetaValue();
        /* Fetching Email Template */
        $emailObj = $em->getRepository('Admin\Entity\Email')->findOneBy(array('keydata' => $emailKey, 'isActive' => 1));

        if (!empty($userId)) {
            /* Fetching User */
            $userObj = $em->getRepository('Admin\Entity\Users')->find($userId);
        }
        if (!empty($emailObj)) {
            $emailSubject = $emailObj->getSubject();
            $mailContent = $emailObj->getContent();
            foreach ($tags as $key => $value) {
                //replace the Macros defined in email body with values
                $mailContent = str_replace($key, $value, $mailContent);
            }
            $mail = new Mail\Message();

            $html = new MimePart($mailContent);
            $html->type = "text/html";

            $body = new MimeMessage();
            $body->setParts(array($html,));

            $attachment = array();

            if ($filename != "") {

                $fileContent = fopen($filename, 'r');
                $attachment = new Mime\Part($fileContent);
                $attachment->type = 'image/jpg';
                $attachment->filename = 'image-file-name.jpg';
                $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                // Setting the encoding is recommended for binary data
                $attachment->encoding = Mime::ENCODING_BASE64;

                $body->setParts(array($html, $attachment));
            }

            /* Set Email Body */
            $mail->setBody($body);

            /* Set Email From Address */
            $mail->setFrom($adminemail, 'Support');

            if (!empty($userId)) {
                $mail->addTo($userObj->getEmail(), $userObj->getFirstName() . " " . $userObj->getLastName());
            } else {
                /* Need to replace with admin email if need to send email to admin */
                $mail->addTo($supportemail, "tapetickets");
            }
            $mail->setSubject($emailSubject); /* Set Email Subject */

            $transport = new Mail\Transport\Sendmail();
            $transport->send($mail);
            return 1;
        } else {
            return 2;
        }
    }

    /**
     * trimString - Function to return substring from string with given length
     * @param string $string
     * @param integer $length
     * @return string|null
     */
    public function trimString($string = null, $length = 10) {
        if ($string != null) {
            if (strlen($string) > $length) {
                $string = substr($string, 0, $length);
                $string .= "...";
            }
        }
        return $string;
    }

    /* getBasePathOfProj - Return the base path of the project with http://
     * @return string
     */

    public function getBasePathOfProj() {
        $uri = $this->getController()->getRequest()->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $path = $this->getController()->getRequest()->getBasePath();
        $base = sprintf('%s://%s%s/', $scheme, $host, $path);

        return $base;
    }

    /**
     * getSchemeHostOfProj - Return Scheme Host of the project
     * @return string
     */
    public function getSchemeHostOfProj() {
        $uri = $this->getController()->getRequest()->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $base = sprintf('%s://%s', $scheme, $host);
        return $base;
    }

    /** Function to get Category Events
     *  @author Aditya
     */
    public function getSearchEvents($type, $val, $offset = null, $limit = null) {
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $tmpevent = array();
        //getCategoryEvent                
        $events = $em->getRepository('\Admin\Entity\Event')->getSearchEvent($type, $val, $offset, $limit);
        $i = 0;
        foreach ($events as $event) {
            $tmpevent[$i]['eventid'] = $event->getId();
            $tmpevent[$i]['title'] = $event->getEventName();
            $tmpevent[$i]['img'] = $event->getEventImageMedium();
            $tmpevent[$i]['artist'] = $event->getEventArtist();
            $tmpevent[$i]['icon'] = $event->getCategory()->getIcon();
            $tmpevent[$i]['venue'] = $event->getEventVenueTitle();
            $tmpevent[$i]['event'] = $event;
            ++$i;
        }
        return $tmpevent;
    }

    /**
     * Function to get all events used while top bar searching
     * @author Aditya
     */
    public function getAllEvents() {
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $events = $em->getRepository('\Admin\Entity\Event')->getAllEvents();
        $i = 0;
        $tmpevent = array();
        $tmpartist = array();
        $tmpvenue = array();
        foreach ($events as $event) {
            $tmpevent[$i]['id'] = $event->getId();
            $tmpevent[$i]['title'] = $event->getEventName();
            $tmpartist[$i]['name'] = $event->getEventArtist();
            $tmpvenue[] = $event->getEventVenueTitle();
            ++$i;
        }

        echo json_encode(array('event' => $tmpevent, 'artist' => $tmpartist, 'venue' => array_unique($tmpvenue)));
        die;
    }

    /** Function to get city on basis of IP Address
     * @return int Cityid
     * @author Aditya
     */
    public function getCityFromIP($ipaddress = "") {
        //Array ( [domain] => dslb-094-219-040-096.pools.arcor-ip.net [country] => DE - Germany [state] => Hessen [town] => Erzhausen )
        //Get an array with geoip-infodata       
        //check, if the provided ip is valid
        $city = "";
        if (!filter_var($ipaddress, FILTER_VALIDATE_IP)) {
            //throw new InvalidArgumentException("IP is not valid");
            return $city;
        }

        //contact ip-server
        /* $response = @file_get_contents('http://www.netip.de/search?query=' . $ipaddress);
          if (empty($response)) {
          throw new InvalidArgumentException("Error contacting Geo-IP-Server");
          }
          //Array containing all regex-patterns necessary to extract ip-geoinfo from page
          $patterns = array();
          $patterns["state"] = '#State/Region: (.*?)<br#i';
          $patterns["town"] = '#City: (.*?)<br#i';

          //Array where results will be stored
          $ipInfo = array();

          //check response from ipserver for above patterns
          foreach ($patterns as $key => $pattern) {
          //store the result in array
          $ipInfo[$key] = preg_match($pattern, $response, $value) && !empty($value[1]) ? $value[1] : 'not found';
          } */

        $iptolocation = 'http://geoip.prototypeapp.com/api/locate?ip=' . $ipaddress;
        //$iptolocation = 'http://geoip.prototypeapp.com/api/locate?ip=80.227.53.178';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $iptolocation);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $jsonString = curl_exec($ch);
        curl_close($ch);

        $geoipData = json_decode($jsonString, true);

        if (!empty($geoipData)) {
            $locationAddress = $geoipData['location']['address'];
            $locationCoordinates = $geoipData['location']['coords'];
            $city = $locationAddress['city'];
            //$city = "";
            if (empty($city)) {
                $lat = $locationCoordinates['latitude'];
                $long = $locationCoordinates['longitude'];
                if (($lat != 0) && ($long != 0)) {
                    $googlegeomapurl = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $lat . "," . $long . "&sensor=false";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $googlegeomapurl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $jsonStringGoogle = curl_exec($ch);
                    curl_close($ch);
                    $googlegeoData = json_decode($jsonStringGoogle, true);

                    if (!empty($googlegeoData)) {
                        if ($googlegeoData['status'] == "OK") {
                            $addressComponentsArr = $googlegeoData['results'][0]['address_components'];
                            //print_r($addressComponentsArr);
                            foreach ($addressComponentsArr as $index => $val) {
                                if (in_array("locality", $val['types'])) {
                                    $city = $val["long_name"];
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $city;
    }

    /**
     * getUserSeatCount - This function returns the number of seat reserved for 
     * any particular date and time for an event.
     * @param integer $eventId
     * @param integer $eventSeat
     * @param string $eventDate
     * @param string $eventTime
     * @return int
     * @author: Manu
     */
    public function getUserSeatCount($eventId, $eventSeat, $eventDate, $eventTime) {

        $em = $this->getEntityManager(); /* Call Entity Manager */

        $userBookingObj = $em->getRepository('\Admin\Entity\UserBooking')
                ->getUsersEventBooking($eventId, $eventSeat, $eventDate, $eventTime);

        if (!empty($userBookingObj)) {

            return $userBookingObj[0][1];
        }
        return 0;
    }

    /**
     * getUserSeatCount - This function returns the number of seat reserved for 
     * any particular date and time for an event.Also it fetches the count even if the payment is failed
     * @param integer $eventId
     * @param integer $eventSeat
     * @param string $eventDate
     * @param string $eventTime
     * @return int
     * @author: Manu
     */
    public function getTotalSeatCount($eventId, $eventSeat, $eventDate, $eventTime) {

        $em = $this->getEntityManager(); /* Call Entity Manager */

        $userBookingObj = $em->getRepository('\Admin\Entity\UserBooking')
                ->getTotalOfEventBooking($eventId, $eventSeat, $eventDate, $eventTime);

        if (!empty($userBookingObj)) {

            return $userBookingObj[0][1];
        }
        return 0;
    }

}
