<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserBooking
 *
 * @ORM\Table(name="user_booking", indexes={@ORM\Index(name="FK_user_booking_userid", columns={"user_id"}), @ORM\Index(name="FK_user_booking_eventid", columns={"event_id"})})
 * @ORM\Entity(repositoryClass="UserBookingRepository")
 */
class UserBooking {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="event_date", type="date", nullable=false)
     */
    private $eventDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="event_time", type="time", nullable=false)
     */
    private $eventTime;

    /**
     * @var string
     *
     * @ORM\Column(name="booking_order_no", type="string", length=20)
     */
    private $bookingOrderNo;

    /**
     * @var integer
     *
     * @ORM\Column(name="booking_seat_count", type="integer", nullable=false)
     */
    private $bookingSeatCount;

    /**
     * @var string
     *
     * @ORM\Column(name="booking_total_price", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $bookingTotalPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=250)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_no", type="string", length=10)
     */
    private $phoneNo;

    /**
     * @var string
     *
     * @ORM\Column(name="card_type", type="string", length=50)
     */
    private $cardType;

    /**
     * @var string
     *
     * @ORM\Column(name="card_no", type="string", length=16)
     */
    private $cardNo;

    /**
     * @var string
     *
     * @ORM\Column(name="expiry_month", type="string", length=2)
     */
    private $expiryMonth;

    /**
     * @var string
     *
     * @ORM\Column(name="expiry_year", type="string", length=4)
     */
    private $expiryYear;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=250)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=250)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="street_address", type="string", length=500)
     */
    private $streetAddress;

    /**
     * @var integer
     *
     * @ORM\Column(name="country", type="integer")
     */
    private $country;

    /**
     * @var integer
     *
     * @ORM\Column(name="city", type="integer")
     */
    private $city;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="pay_id", type="string", length=50)
     */
    private $payId;

    /**
     *
     * @var type string basket_id
     * @ORM\Column(name="dtcm_basket_id", type="string", length=255)
     */
    private $basketId;

    /**
     *
     * @var type string 
     * @ORM\Column(name="dtcm_order_id", type="string", length=255)
     */
    private $orderId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="booking_made_date", type="datetime", nullable=false)
     */
    private $bookingMadeDate;

    /**
     * @var \Admin\Entity\Event
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    private $event;

    /**
     * @var \Admin\Entity\Users
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \Admin\Entity\SeatOrder
     * 
     * @ORM\OneToMany(targetEntity="Admin\Entity\SeatOrder", mappedBy="booking")
     * @ORM\JoinColumn(name="id", referencedColumnName="booking_id")
     */
    private $seatOrder;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set eventDate
     *
     * @param \DateTime $eventDate
     *
     * @return UserBooking
     */
    public function setEventDate($eventDate) {
        $this->eventDate = $eventDate;

        return $this;
    }

    /**
     * Get eventDate
     *
     * @return \DateTime
     */
    public function getEventDate() {
        return $this->eventDate;
    }

    /**
     * Set eventTime
     *
     * @param \DateTime $eventTime
     *
     * @return UserBooking
     */
    public function setEventTime($eventTime) {
        $this->eventTime = $eventTime;

        return $this;
    }

    /**
     * Get eventTime
     *
     * @return \DateTime
     */
    public function getEventTime() {
        return $this->eventTime;
    }

    /**
     * Set bookingOrderNo
     *
     * @param string $bookingOrderNo
     *
     * @return UserBooking
     */
    public function setBookingOrderNo($bookingOrderNo) {
        $this->bookingOrderNo = $bookingOrderNo;

        return $this;
    }

    /**
     * Get bookingOrderNo
     *
     * @return string
     */
    public function getBookingOrderNo() {
        return $this->bookingOrderNo;
    }

    /**
     * Set bookingSeatCount
     *
     * @param integer $bookingSeatCount
     *
     * @return UserBooking
     */
    public function setBookingSeatCount($bookingSeatCount) {
        $this->bookingSeatCount = $bookingSeatCount;

        return $this;
    }

    /**
     * Get bookingSeatCount
     *
     * @return integer
     */
    public function getBookingSeatCount() {
        return $this->bookingSeatCount;
    }

    /**
     * Set bookingTotalPrice
     *
     * @param string $bookingTotalPrice
     *
     * @return UserBooking
     */
    public function setBookingTotalPrice($bookingTotalPrice) {
        $this->bookingTotalPrice = $bookingTotalPrice;

        return $this;
    }

    /**
     * Get bookingTotalPrice
     *
     * @return string
     */
    public function getBookingTotalPrice() {
        return $this->bookingTotalPrice;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return UserBooking
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
     * Set phoneNo
     *
     * @param string $phoneNo
     *
     * @return UserBooking
     */
    public function setPhoneNo($phoneNo) {
        $this->phoneNo = $phoneNo;

        return $this;
    }

    /**
     * Get phoneNo
     *
     * @return string
     */
    public function getPhoneNo() {
        return $this->phoneNo;
    }

    /**
     * Set cardType
     *
     * @param string $cardType
     *
     * @return UserBooking
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
     * Set cardNo
     *
     * @param string $cardNo
     *
     * @return UserBooking
     */
    public function setCardNo($cardNo) {
        $this->cardNo = $cardNo;
        return $this;
    }

    /**
     * Get cardNo
     *
     * @return string
     */
    public function getCardNo() {
        return $this->cardNo;
    }

    /**
     * Set expiryMonth
     *
     * @param string $expiryMonth
     *
     * @return UserBooking
     */
    public function setExpiryMonth($expiryMonth) {
        $this->expiryMonth = $expiryMonth;

        return $this;
    }

    /**
     * Get expiryMonth
     *
     * @return string
     */
    public function getExpiryMonth() {
        return $this->expiryMonth;
    }

    /**
     * Set expiryYear
     *
     * @param string $expiryYear
     *
     * @return UserBooking
     */
    public function setExpiryYear($expiryYear) {
        $this->expiryYear = $expiryYear;

        return $this;
    }

    /**
     * Get expiryYear
     *
     * @return string
     */
    public function getExpiryYear() {
        return $this->expiryYear;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return UserBooking
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
     * @return UserBooking
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
     * Set streetAddress
     *
     * @param string $streetAddress
     *
     * @return UserBooking
     */
    public function setStreetAddress($streetAddress) {
        $this->streetAddress = $streetAddress;

        return $this;
    }

    /**
     * Get streetAddress
     *
     * @return string
     */
    public function getStreetAddress() {
        return $this->streetAddress;
    }

    /**
     * Set country
     *
     * @param integer $country
     *
     * @return UserBooking
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
     * @return UserBooking
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
     * Set status
     *
     * @param integer $status
     *
     * @return UserBooking
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
     * Set payId
     *
     * @param string $payId
     *
     * @return UserBooking
     */
    public function setPayId($payId) {
        $this->payId = $payId;

        return $this;
    }

    /**
     * Get payId
     *
     * @return string
     */
    public function getPayId() {
        return $this->payId;
    }

    /**
     * get Basket Id
     * @return type string
     */
    public function getBasketId() {
        return $this->basketId;
    }

    /**
     * set Basket Id
     * @param type $basketId
     * @return \Admin\Entity\UserBooking
     */
    public function setBasketId($basketId) {
        $this->basketId = $basketId;
        return $this;
    }

    /**
     * get Order Id
     * @return type string
     */
    public function getOrderId() {
        return $this->orderId;
    }

    /**
     * set Order Id
     * @param type $orderId
     * @return \Admin\Entity\UserBooking
     */
    public function setOrderId($orderId) {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * Set bookingMadeDate
     *
     * @param \DateTime $bookingMadeDate
     *
     * @return UserBooking
     */
    public function setBookingMadeDate($bookingMadeDate) {
        $this->bookingMadeDate = $bookingMadeDate;

        return $this;
    }

    /**
     * Get bookingMadeDate
     *
     * @return \DateTime
     */
    public function getBookingMadeDate() {
        return $this->bookingMadeDate;
    }

    /**
     * Set event
     *
     * @param \Admin\Entity\Event $event
     *
     * @return UserBooking
     */
    public function setEvent(\Admin\Entity\Event $event = null) {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \Admin\Entity\Event
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * Get SeatOrder
     *
     * @return \Admin\Entity\SeatOrder
     */
    public function getSeatOrder() {
        return $this->seatOrder;
    }

    /**
     * Set user
     *
     * @param \Admin\Entity\Users $user
     *
     * @return UserBooking
     */
    public function setUser(\Admin\Entity\Users $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Admin\Entity\Users
     */
    public function getUser() {
        return $this->user;
    }

}
