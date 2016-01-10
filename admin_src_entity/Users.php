<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="UsersRepository")
 */
class Users {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=500, nullable=true)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="user_type", type="string", length=5, nullable=true)
     */
    private $userType;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=500, nullable=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=100, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=100, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=10, nullable=true)
     */
    private $phone;

    /**
     * @var integer
     *
     * @ORM\Column(name="country", type="integer", nullable=true)
     */
    private $country;

    /**
     * @var integer
     *
     * @ORM\Column(name="city", type="integer", nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string", length=10, nullable=true)
     */
    private $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(name="card_type", type="string", length=40, nullable=true)
     */
    private $cardType;

    /**
     * @var string
     *
     * @ORM\Column(name="credit_card_no", type="string", length=20, nullable=true)
     */
    private $creditCardNo;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=50, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=50, nullable=true)
     */
    private $longitude;

    /**
     * @var integer
     *
     * @ORM\Column(name="fb_user", type="smallint", nullable=true)
     */
    private $fbUser;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=true)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_forgot_status", type="smallint", nullable=true)
     */
    private $isForgotStatus = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_date", type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @var \Admin\Entity\UserRole
     * @ORM\ManyToOne(targetEntity="Admin\Entity\UserRole")
     * @ORM\JoinColumn(name="role", referencedColumnName="id")
     * 
     */
    private $userRole;

    /**
     * @var \Admin\Entity\UserRole
     * 
     * @ORM\Column(name="secret_key", type="string", length=25, nullable=true)
     * 
     */
    private $secretKey;

    /**
     * @var \Admin\Entity\UserRole
     * 
     * @ORM\Column(name="auth_token", type="string", length=25, nullable=true)
     * 
     */
    private $authToken;

    /**
     *
     * @var type integer
     * @ORM\Column(name="dtcm_customer_id", type="integer", nullable=true)
     */
    private $dtcmCustomerId;

    /**
     *
     * @var type integer
     * @ORM\Column(name="dtcm_customer_account", type="integer", nullable=true)
     */
    private $dtcmCustomerAccount;

    /**
     *
     * @var type string
     * @ORM\Column(name="dtcm_customer_afile", type="string", length=10, nullable=true)
     */
    private $dtcmCustomerAfile;

    /**
     *
     * @var type string
     * @ORM\Column(name="salutation", type="string", length=10, nullable=true)
     */
    private $salutation;

    /**
     *
     * @var type string 
     * @ORM\Column(name="nationality", type="string", length=10, nullable=true)
     */
    private $nationality;

    /**
     *
     * @var \DateTime
     * @ORM\Column(name="dateofbirth", type="date", nullable=true)
     */
    private $dateofbirth;

    /**
     *
     * @var type integer
     * @ORM\Column(name="internationalcode", type="integer", nullable=true)
     */
    private $internationalcode;

    /**
     *
     * @var type string
     * @ORM\Column(name="addressline1", type="string", length=255, nullable=true)
     */
    private $addressline1;

    /**
     *
     * @var type string
     * @ORM\Column(name="addressline2", type="string", length=255, nullable=true)
     */
    private $addressline2;

    /**
     *
     * @var type string
     * @ORM\Column(name="addressline3", type="string", length=255, nullable=true)
     */
    private $addressline3;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Users
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Users
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Set userType
     *
     * @param string $userType
     *
     * @return Users
     */
    public function setUserType($userType) {
        $this->userType = $userType;

        return $this;
    }

    /**
     * Get userType
     *
     * @return string
     */
    public function getUserType() {
        return $this->userType;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Users
     */
    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Users
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Users
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Users
     */
    public function setPhone($phone) {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * Set country
     *
     * @param integer $country
     *
     * @return Users
     */
    public function setCountry($country) {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return integer
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Set city
     *
     * @param integer $city
     *
     * @return Users
     */
    public function setCity($city) {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return integer
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return Users
     */
    public function setZipCode($zipCode) {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode() {
        return $this->zipCode;
    }

    /**
     * Set cardType
     *
     * @param string $cardType
     *
     * @return Users
     */
    public function setCardType($cardType) {
        $this->cardType = $cardType;

        return $this;
    }

    /**
     * Get cardType
     *
     * @return string
     */
    public function getCardType() {
        return $this->cardType;
    }

    /**
     * Set creditCardNo
     *
     * @param string $creditCardNo
     *
     * @return Users
     */
    public function setCreditCardNo($creditCardNo) {
        $this->creditCardNo = $creditCardNo;

        return $this;
    }

    /**
     * Get creditCardNo
     *
     * @return string
     */
    public function getCreditCardNo() {
        return $this->creditCardNo;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Users
     */
    public function setLatitude($latitude) {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude() {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Users
     */
    public function setLongitude($longitude) {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude() {
        return $this->longitude;
    }

    /**
     * Set fbUser
     *
     * @param integer $fbUser
     *
     * @return Users
     */
    public function setFbUser($fbUser) {
        $this->fbUser = $fbUser;

        return $this;
    }

    /**
     * Get fbUser
     *
     * @return integer
     */
    public function getFbUser() {
        return $this->fbUser;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Users
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set isForgotStatus
     *
     * @param integer $isForgotStatus
     *
     * @return Users
     */
    public function setIsForgotStatus($isForgotStatus) {
        $this->isForgotStatus = $isForgotStatus;

        return $this;
    }

    /**
     * Get isForgotStatus
     *
     * @return integer
     */
    public function getIsForgotStatus() {
        return $this->isForgotStatus;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Users
     */
    public function setCreatedDate($createdDate) {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate() {
        return $this->createdDate;
    }

    /**
     * Set updatedDate
     *
     * @param \DateTime $updatedDate
     *
     * @return Users
     */
    public function setUpdatedDate($updatedDate) {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    /**
     * Get updatedDate
     *
     * @return \DateTime
     */
    public function getUpdatedDate() {
        return $this->updatedDate;
    }

    /**
     * Set UserRole
     *
     * @param \Admin\Entity\UserRole $role
     *
     * @return UserRole
     */
    public function setUserRole(\Admin\Entity\UserRole $role = null) {
        $this->userRole = $role;

        return $this;
    }

    /**
     * Get UserRole
     *
     * @return \Admin\Entity\UserRole
     */
    public function getUserRole() {
        return $this->userRole;
    }

    /**
     * Set secretKey
     *
     * @param string $secretKey
     *
     * @return Users
     */
    public function setSecretKey($secretKey) {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Get secretKey
     *
     * @return string
     */
    public function getSecretKey() {
        return $this->secretKey;
    }

    /**
     * Set authToken
     *
     * @param string $authToken
     *
     * @return Users
     */
    public function setAuthToken($authToken) {
        $this->authToken = $authToken;
        return $this;
    }

    /**
     * Get authToken
     *
     * @return string
     */
    public function getAuthToken() {
        return $this->authToken;
    }

    /**
     * get Dtcm Customer Id
     * @return type integer
     */
    public function getDtcmCustomerId() {
        return $this->dtcmCustomerId;
    }

    /**
     * set Dtcm Customer Id
     * @param type $dtcmCustomerId
     * @return \Admin\Entity\Users
     */
    public function setDtcmCustomerId($dtcmCustomerId) {
        $this->dtcmCustomerId = $dtcmCustomerId;
        return $this;
    }

    /**
     * get Dtcm Customer Account
     * @return type integer
     */
    public function getDtcmCustomerAccount() {
        return $this->dtcmCustomerAccount;
    }

    /**
     * set Dtcm Customer Account
     * @param type $dtcmCustomerAccount
     * @return \Admin\Entity\Users
     */
    public function setDtcmCustomerAccount($dtcmCustomerAccount) {
        $this->dtcmCustomerAccount = $dtcmCustomerAccount;
        return $this;
    }

    /**
     * get Dtcm Customer Afile
     * @return type string
     */
    public function getDtcmCustomerAfile() {
        return $this->dtcmCustomerAfile;
    }

    /**
     * set Dtcm Customer Afile
     * @param type $dtcmCustomerAfile
     * @return \Admin\Entity\Users
     */
    public function setDtcmCustomerAfile($dtcmCustomerAfile) {
        $this->dtcmCustomerAfile = $dtcmCustomerAfile;
        return $this;
    }

    /**
     * get Salutation
     * @return type string
     */
    public function getSalutation() {
        return $this->salutation;
    }

    /**
     * set Salutation
     * @param type $salutation
     * @return \Admin\Entity\Users
     */
    public function setSalutation($salutation) {
        $this->salutation = $salutation;
        return $this;
    }

    /**
     * get Nationality 
     * @return type string 
     */
    public function getNationality() {
        return $this->nationality;
    }

    /**
     * set Nationality 
     * @param type $nationality
     * @return \Admin\Entity\Users
     */
    public function setNationality($nationality) {
        $this->nationality = $nationality;
        return $this;
    }

    /**
     * get Date Of Birth
     * @return \DateTime
     */
    public function getDateOfBirth() {
        return $this->dateofbirth;
    }

    /**
     * set Date Of Birth
     * @param type $dateofbirth
     * @return \Admin\Entity\Users
     */
    public function setDateOfBirth($dateofbirth) {
        $this->dateofbirth = $dateofbirth;
        return $this;
    }

    /**
     * get International Code
     * @return type string
     */
    public function getInternationalCode() {
        return $this->internationalcode;
    }

    /**
     * set International Code
     * @param type $internationalcode
     * @return \Admin\Entity\Users
     */
    public function setInternationalCode($internationalcode) {
        $this->internationalcode = $internationalcode;
        return $this;
    }

    /**
     * get Addressline One
     * @return type string
     */
    public function getAddresslineOne() {
        return $this->addressline1;
    }

    /**
     * set Addressline One
     * @param type $addressline1
     * @return \Admin\Entity\Users
     */
    public function setAddresslineOne($addressline1) {
        $this->addressline1 = $addressline1;
        return $this;
    }

    /**
     * get Addressline Two
     * @return type string
     */
    public function getAddresslineTwo() {
        return $this->addressline2;
    }

    /**
     * set Addressline Two
     * @param type $addressline2
     * @return \Admin\Entity\Users
     */
    public function setAddresslineTwo($addressline2) {
        $this->addressline2 = $addressline2;
        return $this;
    }

    /**
     * get Addressline Three
     * @return type string
     */
    public function getAddresslineThree() {
        return $this->addressline3;
    }

    /**
     * set Addressline Three
     * @param type $addressline3
     * @return \Admin\Entity\Users
     */
    public function setAddresslineThree($addressline3) {
        $this->addressline2 = $addressline3;
        return $this;
    }

}
