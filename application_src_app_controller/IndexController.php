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
use Zend\Session\SessionManager;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Application\Form as Forms;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Zend\Mail as Mail;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class IndexController extends AbstractActionController {

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
        if (!empty($userid)) {
            $msg = 'You are already logged in.';
            $status = 1;
            $this->layout()->setVariable('userId', $user_session->userId);
            $this->layout()->setVariable('username', $user_session->userName);
            $username = $user_session->userName;
            $tmp_user = $em->getRepository('\Admin\Entity\Users')->find($user_session->userId);
            $city = $tmp_user->getCity();
            if (!empty($city)) {
                $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                $this->layout()->userCity = $cityObj->getCityName();
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

    public function indexAction() {
        $this->layout()->pageTitle = "Home";
        $em = $this->getEntityManager();
        $cities = $em->getRepository('\Admin\Entity\City')->findBy(array('countryId' => 2));
        $categories = $em->getRepository('\Admin\Entity\Categories')->findBy(array('status' => 1));
        $signupForm = new Forms\SignupForm();
        $loginForm = new Forms\LoginForm();
        $forgotpassForm = new Forms\ForgotPasswordForm();
        $this->layout()->signupForm = $signupForm;
        $this->layout()->loginForm = $loginForm;
        $this->layout()->forgotpassForm = $forgotpassForm;
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $city = "";
        if (!empty($userid)) {
            $tmp_user = $em->getRepository('\Admin\Entity\Users')->find($user_session->userId);
            $city = $tmp_user->getCity();
            if (!empty($city)) {
                $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                $this->layout()->userCity = $cityObj->getCityName();
            }
        }
        $request = $this->getRequest();
        $signupError = 0;   /* Variable to maintain status on sign up pop up */
        $succsMsg = "";
        if ($request->isPost()) {
            $formValidator = new Forms\Validator\SignupFormValidator();
            /* Sign up Form Validator Object */
            $signupForm->setInputFilter($formValidator->getInputFilter()); /* Apply filter to form */
            $signupForm->setData($request->getPost()); /* set post data to form */
            $data = $this->getRequest()->getPost(); /* Fetch Post Variables */
            // check if form is valid
            if ($signupForm->isValid()) {
                $checkIfEmailAlreadyExist = $em->getRepository('\Admin\Entity\Users')
                        ->checkIfEmailAlreadyExist($data['email']);
                if (empty($checkIfEmailAlreadyExist)) {
                    $currentDate = date_create(date('Y-m-d H:i:s'));
                    $userObj = new Entities\Users();
                    $userObj->setEmail($data['email']);
                    $userObj->setFirstName($data['fname']);
                    $userObj->setPassword(md5($data['password']));
                    $userObj->setStatus(1);
                    $userObj->setFbUser(0);
                    $userObj->setUserType("N");
                    $userObj->setCreatedDate($currentDate);
                    $userObj->setUpdatedDate($currentDate);
                    $em->persist($userObj);
                    $em->flush();

                    $commonPlugin = $this->Common();
                    $web_url = $commonPlugin->getBasePathOfProj();
                    $arrMacros = array('$FIRSTNAME' => $data['fname'], '$LASTNAME' => $data['lname'], '$EMAIL' => $data['email'], '$PASSWORD' => $data['password'], '$WEBURL' => $web_url);
                    $emailSent = $commonPlugin->sendEmail($arrMacros, 'admin_register_user', $userObj->getId());

                    //$flashMessenger = $this->flashMessenger();                     
                    //$flashMessenger->setNamespace('success');
                    //$flashMessenger->addMessage("You have signed up successfully. You can now login into the website.");
                    $succsMsg = "You have signed up successfully. Please click on below link to Login into your account";
                    $elements = $signupForm->getElements();
                    foreach ($elements as $element) {
                        if ($element instanceof \Zend\Form\Element\Text) {
                            $element->setValue('');
                        }

                        // Other element types here
                    }
                    $signupError = 1;
                } else {
                    $signupForm->get('email')->setMessages(array('Email Id already exists'));
                    $signupError = 1;
                }
            } else {

                $signupError = 1;
            }
        }
        $featuredEvents = $em->getRepository('Admin\Entity\Event')->getFeaturedEvent();
        if ($city == "") {
            $ip = "";
            /* if (isset($_SERVER['HTTP_CLIENT_IP']) && ($_SERVER["HTTP_CLIENT_IP"]!=""))
              $ip = $_SERVER['HTTP_CLIENT_IP'];
              else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && ($_SERVER["HTTP_X_FORWARDED_FOR"]!="")){
              if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
              $addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
              $ip = trim($addr[0]);
              } else {
              $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
              }
              //$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
              }
              else if(isset($_SERVER['HTTP_X_FORWARDED']) && ($_SERVER["HTTP_X_FORWARDED"]!=""))
              $ip = $_SERVER['HTTP_X_FORWARDED'];
              else if(isset($_SERVER['HTTP_FORWARDED_FOR']) && ($_SERVER["HTTP_FORWARDED_FOR"]!=""))
              $ip = $_SERVER['HTTP_FORWARDED_FOR'];
              else if(isset($_SERVER['HTTP_FORWARDED']) && ($_SERVER["HTTP_FORWARDED"]!=""))
              $ip = $_SERVER['HTTP_FORWARDED'];
              else
              $ip = $_SERVER['REMOTE_ADDR']; */

            //$response = @file_get_contents('https://ip.anysrc.net/json');
            //echo "<pre>";
            //print_r($response);die;
            $ip = $_SERVER['REMOTE_ADDR'];
            $commonPlugin = $this->Common();
            //$web_url = $commonPlugin->getCityFromIP($ip);
            $city = $commonPlugin->getCityFromIP($ip);
            //echo $city;
            //print_r($web_url) ;
            /* if(trim($web_url['state']) != "not found"){            
              $cityobj = $em->getRepository('Admin\Entity\City')->findBy(array('cityName' => trim($web_url['state'])));
              if(empty($cityobj)){
              if(trim($web_url['town']) != "not found"){
              $cityobj = $em->getRepository('Admin\Entity\City')->findBy(array('cityName' => trim($web_url['town'])));
              $cityobj = $cityobj[0];
              }else{
              $cityobj = "";
              }
              }else{
              $cityobj = $cityobj[0];
              }
              }elseif(trim($web_url['town']) != "not found"){
              $cityobj = $em->getRepository('Admin\Entity\City')->findBy(array('cityName' => trim($web_url['city'])));
              $cityobj = $cityobj[0];
              }else{
              $cityobj = "";
              } */
            if (!empty($city)) {
                $cityobj = $em->getRepository('Admin\Entity\City')->findOneBy(array('cityName' => trim($city)));
            } else {
                /* If no city found by IP Address then it will show the default City Events named Dubai */
                $cityobj = $em->getRepository("Admin\Entity\City")->findOneBy(array('cityName' => "Dubai"));
            }
        } else {
            $cityobj = $em->getRepository('Admin\Entity\City')->find($city);
        }
        if (!empty($cityobj)) {
            $nearbyEvents = $em->getRepository('Admin\Entity\Event')->getHomePageEvents($cityobj);
        } else {
            $nearbyEvents = "";
        }
        $this->layout()->featuredEvents = $featuredEvents;
        $this->layout()->signupError = $signupError;
        $this->layout()->succsMsg = $succsMsg;
        $searchSession = new Container("searchsess");
        if ($searchSession->offsetExists('searchType') || $searchSession->offsetExists('searchTerm')) {
            $searchSession->getManager()->getStorage()->clear('searchsess');
        }
        return new ViewModel(array('cities' => $cities, 'categories' => $categories, 'nearbyEvents' => $nearbyEvents));
    }

    /**
     * 
     * @return string 1|0 
     */
    public function checkemailAction() {

        $email = $this->params('email');
        if (empty($email)) {
            return $this->getResponse()->setContent("2"); /* Email Id not received */
        } else {
            $em = $this->getEntityManager();
            $checkIfEmailExist = $em->getRepository('\Admin\Entity\Users')->checkIfEmailAlreadyExist($email);
            if (!empty($checkIfEmailExist)) {
                return $this->getResponse()->setContent("1");
                /* Means Email Exists */
            }
        }
        return $this->getResponse()->setContent("0");
    }

    /**
     * Save FB details.
     */
    public function connectAction() {
        $request = $this->getRequest();
        $em = $this->getEntityManager();
        if ($request->isPost()) {
            $postedData = $request->getPost();
            $email = $postedData['email'];
            if (!empty($email)) {
                $userObj = $em->getRepository('\Admin\Entity\Users')->checkIfEmailAlreadyExist($email);
                if (!empty($userObj)) {
                    $commonHelper = $this->Common();
                    $user_session = new Container('user');
                    $user_session->userId = $userObj[0]['id'];
                    $user_session->userName = $commonHelper->trimString($postedData['name'], 15);
                    echo "1";
                } else {
                    $password = rand(100000, 999999);
                    $currentDate = date_create(date('Y-m-d H:i:s'));
                    $userObj = new Entities\Users();
                    $userObj->setEmail($email);
                    $userObj->setFirstName($postedData['name']);
                    $userObj->setPassword(md5($password));
                    $userObj->setStatus(1);
                    $userObj->setFbUser(1);
                    $userObj->setUserType("N");
                    $userObj->setCreatedDate($currentDate);
                    $userObj->setUpdatedDate($currentDate);
                    $em->persist($userObj);
                    $em->flush();
                    $commonPlugin = $this->Common();
                    $arrMacros = array('$FIRSTNAME' => $postedData['name'], '$LASTNAME' => '', '$EMAIL' => $email, '$PASSWORD' => $password, '$WEBURL' => $web_url);
                    $emailSent = $commonPlugin->sendEmail($arrMacros, 'admin_register_user', $userObj->getId());
                    $flashMessenger = $this->flashMessenger();
                    $flashMessenger->setNamespace('success');
                    $flashMessenger->addMessage("You have signed up successfully. You can now login into the website.");
                    echo "2";
                }
            }
            exit();
        } else {
            
        }
        exit();
    }

    /**
     * Function to process login 
     * @author Aditya
     */
    public function userloginAction() {
        /** New Code * */
        $messages = array();
        $em = $this->getEntityManager();
        $formData = $this->getRequest()->getPost()->toArray();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $form = new Forms\LoginForm();
        $request = $this->getRequest();
        $referrerUrl = "";
        if ($request->isXmlHttpRequest()) {
            $formValidator = new Forms\Validator\LoginFormValidator();
            $form->setInputFilter($formValidator->getInputFilter());
            $form->setData($formData);
            $data = array(
                'email' => $formData['email'],
                'password' => $formData['password']
            );
            if ($form->isValid()) {
                $results = $em->getRepository('Admin\Entity\Users')->verifyUser($data);
                if (!empty($results)) {
                    if ($results[0]['isForgotStatus'] != 2) {
                        $name = $results[0]['firstName'] . ' ' . $results[0]['lastName'];
                        $commobj = $this->Common();
                        if (trim($name) == "") {
                            $name = $commobj->trimString($formData['email'], 12);
                        }
                        $user_session = new Container('user');
                        $user_session->userId = $results[0]['id'];
                        $user_session->userName = $name;
                        if ($results[0]['isForgotStatus'] == 1) {
                            $tmpObj = $em->getRepository('\Admin\Entity\Users')->find($results[0]['id']);
                            $tmpObj->setIsForgotStatus(2);
                            $em->persist($tmpObj);
                            $em->flush();
                            $flashMessenger = $this->flashMessenger();
                            $flashMessenger->setNamespace('success');
                            $msg = "Old Password is the OTP you received in your Email";
                            $status = 1;
                        } else {
                            $msg = "You have been logged in successfully.";
                            $status = 2;
                            $referrerUrl = $this->getRequest()->getHeader('Referer')->getUri();
                        }
                    } else {
                        $msg = "Your OTP has expired. Kindly regenerate your password using Forgot Password Link";
                        $status = 0;
                    }
                } else {
                    $msg = 'Sorry! You have entered an incorrect email or password. Please enter correct login details to proceed';
                    $status = 0;
                }
            } else {
                $msg = 'Kindly recheck your details. It seems to be incorrect';
                $status = 0;
            }
        }
        $tmp_arr = json_encode(array('status' => $status, 'msg' => $msg, 'reffererUrl' => $referrerUrl));
        echo $tmp_arr;
        die;
    }

    /**
     * Function to logout active user session
     * @return int
     * @author Aditya
     */
    public function userlogoutAction() {
        $user_session = new Container('user');
        $userid = $user_session->userId;
        if (!empty($userid)) {
            $user_session->getManager()->getStorage()->clear('user');
            $searchSession = new Container("searchsess");
            if ($searchSession->offsetExists('searchType') || $searchSession->offsetExists('searchTerm')) {
                $searchSession->getManager()->getStorage()->clear('searchsess');
            }
            return 1;
        } else {
            return 0;
        }
    }

    /**
     *  Function for Resetting Forgot Password
     *  @author Aditya
     */
    public function userforgotpasswordAction() {
        $messages = array();
        $em = $this->getEntityManager();
        $formData = $this->getRequest()->getPost()->toArray();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $form = new Forms\ForgotPasswordForm();
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $formValidator = new Forms\Validator\ForgotPasswordFormValidator();
            $form->setInputFilter($formValidator->getInputFilter());
            $form->setData($formData);
            $inputemail = trim($formData['emailaddr']);
            $data = array(
                'email' => $formData['emailaddr']
            );
            if ($form->isValid()) {
                $em = $this->getEntityManager();
                $forgotpasData = $em->getRepository('Admin\Entity\Users')->getUserByEmail($inputemail, 'N');
                if (!empty($forgotpasData)) {
                    //Fetch User to update the usertype status to 2 and password with OTP
                    $tmpObj = $em->getRepository('\Admin\Entity\Users')->find($forgotpasData[0]['id']);
                    $pass = rand(100000, 999999);
                    $enc_pass = md5($pass);
                    $tmpObj->setIsForgotStatus(1);
                    $tmpObj->setPassword($enc_pass);
                    $em->persist($tmpObj);
                    $em->flush(); /* Insert Object into DB */
                    $fname = trim($tmpObj->getFirstname());
                    $lname = trim($tmpObj->getLastname());
                    $username = trim($tmpObj->getUsername());
                    if (($fname == "") && ($lname == "")) {
                        $fname = "User";
                        $lname = "";
                    }
                    $commonPlugin = $this->Common();
                    $web_url = $commonPlugin->getBasePathOfProj();
                    $arrpTags = array(
                        '$FIRSTNAME' => $fname,
                        '$LASTNAME' => $lname,
                        '$PASSWORD' => $pass,
                        '$WEBURL' => $web_url,
                        '$EMAIL' => $inputemail
                    );

                    if (!empty($tmpObj)) {
                        $emailSent = $commonPlugin->sendEmail($arrpTags, 'frontend_forgot_pass', $tmpObj->getId());
                        if ($emailSent == 1) {
                            $status = 1;
                            $msg = 'One time password has been mailed to your Registered email address.';
                        } else {
                            $status = 0;
                            $msg = 'There is some problem in sending Email. Kindly Re-Try after some time.';
                        }
                    } else {
                        $status = 0;
                        $msg = "Seems to be no user is associated with this Email Address. Kindly drop an email to admin@tapetickets.com regarding this issue.";
                    }
                } else {
                    $msg = "This email address is not registered with us";
                    $status = 0;
                }
            }
        }
        $tmp_arr = json_encode(array('status' => $status, 'msg' => $msg));
        echo $tmp_arr;
        die;
    }

    /**
     * Function to display About Us page
     * @author Aditya
     */
    public function aboutusAction() {
        $em = $this->getEntityManager();
        $pageTitle = 'About Us';
        $cmsData = $em->getRepository('Admin\Entity\Cms')->getCmsById(1);
        $keywords = (!empty($cmsData[0]['keywords']) ? $cmsData[0]['keywords'] : $pageTitle);
        $metatag = (!empty($cmsData[0]['metaTag']) ? $cmsData[0]['metaTag'] : $pageTitle);
        $metadesc = (!empty($cmsData[0]['metaDesc']) ? $cmsData[0]['metaDesc'] : $pageTitle);
        $content = $cmsData[0]['content'];
        $this->layout()->setVariable('keywords', $keywords);
        $this->layout()->setVariable('metatag', $metatag);
        $this->layout()->setVariable('metadesc', $metadesc);
        $this->layout()->pageTitle = $pageTitle; /* Setting page title */
        return array('cmsdata' => $content, 'pagetitle' => $pageTitle);
    }

    /**
     * Function to display Terms and Conditions page
     * @return type
     * @author Aditya
     */
    public function termsAction() {
        $em = $this->getEntityManager();
        $cmsData = $em->getRepository('Admin\Entity\Cms')->getCmsById(2);
        $pageTitle = 'Terms & Conditions';
        $keywords = (!empty($cmsData[0]['keywords']) ? $cmsData[0]['keywords'] : $pageTitle);
        $metatag = (!empty($cmsData[0]['metaTag']) ? $cmsData[0]['metaTag'] : $pageTitle);
        $metadesc = (!empty($cmsData[0]['metaDesc']) ? $cmsData[0]['metaDesc'] : $pageTitle);
        $content = $cmsData[0]['content'];
        $this->layout()->setVariable('keywords', $keywords);
        $this->layout()->setVariable('metatag', $metatag);
        $this->layout()->setVariable('metadesc', $metadesc);
        $this->layout()->pageTitle = $pageTitle; /* Setting page title */
        return array('cmsdata' => $content, 'pagetitle' => $pageTitle);
    }

    /**
     *  Function to display FAQ page
     * @return type
     * @author Aditya
     */
    public function faqAction() {
        $em = $this->getEntityManager();
        $cmsData = $em->getRepository('Admin\Entity\Cms')->getCmsById(4);
        $pageTitle = 'FAQ';
        $keywords = (!empty($cmsData[0]['keywords']) ? $cmsData[0]['keywords'] : $pageTitle);
        $metatag = (!empty($cmsData[0]['metaTag']) ? $cmsData[0]['metaTag'] : $pageTitle);
        $metadesc = (!empty($cmsData[0]['metaDesc']) ? $cmsData[0]['metaDesc'] : $pageTitle);
        $content = $cmsData[0]['content'];
        $this->layout()->setVariable('keywords', $keywords);
        $this->layout()->setVariable('metatag', $metatag);
        $this->layout()->setVariable('metadesc', $metadesc);
        $this->layout()->pageTitle = $pageTitle; /* Setting page title */
        return array('cmsdata' => $content, 'pagetitle' => $pageTitle);
    }

		/**
     *  Function to display FAQ page
     * @return type
     * @author Aditya
     */
    public function  privacypolicyAction(){
        $em = $this->getEntityManager();
        $cmsData = $em->getRepository('Admin\Entity\Cms')->getCmsById(7);        
        $pageTitle  = 'Privacy Policy';
        $keywords = (!empty($cmsData[0]['keywords']) ? $cmsData[0]['keywords'] : $pageTitle);
        $metatag = (!empty($cmsData[0]['metaTag']) ? $cmsData[0]['metaTag'] : $pageTitle);
        $metadesc = (!empty($cmsData[0]['metaDesc']) ? $cmsData[0]['metaDesc'] : $pageTitle);
        $content = $cmsData[0]['content'];
        $this->layout()->setVariable('keywords',$keywords );
        $this->layout()->setVariable('metatag',$metatag );
        $this->layout()->setVariable('metadesc',$metadesc );                
        $this->layout()->pageTitle = $pageTitle; /* Setting page title */
        return array('cmsdata'=>$content,'pagetitle'=>$pageTitle);        
    }

     /**
     *  Function to display FAQ page
     * @return type
     * @author Aditya
     */
    public function  deliverypolicyAction(){
        $em = $this->getEntityManager();
        $cmsData = $em->getRepository('Admin\Entity\Cms')->getCmsById(8);        
        $pageTitle  = 'Purchase Policy';
        $keywords = (!empty($cmsData[0]['keywords']) ? $cmsData[0]['keywords'] : $pageTitle);
        $metatag = (!empty($cmsData[0]['metaTag']) ? $cmsData[0]['metaTag'] : $pageTitle);
        $metadesc = (!empty($cmsData[0]['metaDesc']) ? $cmsData[0]['metaDesc'] : $pageTitle);
        $content = $cmsData[0]['content'];
        $this->layout()->setVariable('keywords',$keywords );
        $this->layout()->setVariable('metatag',$metatag );
        $this->layout()->setVariable('metadesc',$metadesc );                
        $this->layout()->pageTitle = $pageTitle; /* Setting page title */
        return array('cmsdata'=>$content,'pagetitle'=>$pageTitle);        
    }

    /**
     * Function to display Printing Ticket Page
     * @return type
     * @author Aditya
     */
    public function printingticketAction() {
        $em = $this->getEntityManager();
        $cmsData = $em->getRepository('Admin\Entity\Cms')->getCmsById(5);
        $pageTitle = 'Printing Tickets';
        $keywords = (!empty($cmsData[0]['keywords']) ? $cmsData[0]['keywords'] : $pageTitle);
        $metatag = (!empty($cmsData[0]['metaTag']) ? $cmsData[0]['metaTag'] : $pageTitle);
        $metadesc = (!empty($cmsData[0]['metaDesc']) ? $cmsData[0]['metaDesc'] : $pageTitle);
        $content = $cmsData[0]['content'];
        $this->layout()->setVariable('keywords', $keywords);
        $this->layout()->setVariable('metatag', $metatag);
        $this->layout()->setVariable('metadesc', $metadesc);
        $this->layout()->pageTitle = $pageTitle; /* Setting page title */
        return array('cmsdata' => $content, 'pagetitle' => $pageTitle);
    }

    /**
     * Function to display Contact us Page
     * @return type
     * @author Aditya
     */
    public function contactusAction() {
        $em = $this->getEntityManager();
        $cmsData = $em->getRepository('Admin\Entity\Cms')->getCmsById(3);
        $pageTitle = 'Contact Us';
        $keywords = (!empty($cmsData[0]['keywords']) ? $cmsData[0]['keywords'] : $pageTitle);
        $metatag = (!empty($cmsData[0]['metaTag']) ? $cmsData[0]['metaTag'] : $pageTitle);
        $metadesc = (!empty($cmsData[0]['metaDesc']) ? $cmsData[0]['metaDesc'] : $pageTitle);
        $content = $cmsData[0]['content'];
        $this->layout()->setVariable('keywords', $keywords);
        $this->layout()->setVariable('metatag', $metatag);
        $this->layout()->setVariable('metadesc', $metadesc);
        $this->layout()->pageTitle = $pageTitle; /* Setting page title */
        return array('cmsdata' => $content, 'pagetitle' => $pageTitle);
    }

    /**
     * Function to display Contact us Page
     * @return type
     * @author Aditya
     */
    public function supportAction() {
        $em = $this->getEntityManager();
        $cmsData = $em->getRepository('Admin\Entity\Cms')->getCmsById(3);
        $pageTitle = 'Support';
        $keywords = (!empty($cmsData[0]['keywords']) ? $cmsData[0]['keywords'] : $pageTitle);
        $metatag = (!empty($cmsData[0]['metaTag']) ? $cmsData[0]['metaTag'] : $pageTitle);
        $metadesc = (!empty($cmsData[0]['metaDesc']) ? $cmsData[0]['metaDesc'] : $pageTitle);
        $content = $cmsData[0]['content'];
        $this->layout()->setVariable('keywords', $keywords);
        $this->layout()->setVariable('metatag', $metatag);
        $this->layout()->setVariable('metadesc', $metadesc);
        $this->layout()->pageTitle = $pageTitle; /* Setting page title */
        return array('cmsdata' => $content, 'pagetitle' => $pageTitle);
    }

    /**
     * Function to check if user is logged in or not during AJAX
     * @author Aditya
     */
    public function checkUserLoggedInOrNot() {
        $user_session = new Container('user');
        $userid = $user_session->userId;
        if (empty($userid)) {
            /* if not logged in redirect the user to login page */
            return $this->redirect()->toRoute('home');
        }
    }

    public function getSearchableEventsAction() {
        $commonPlugin = $this->Common();
        $events = $commonPlugin->getAllEvents();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $events;
    }

}
