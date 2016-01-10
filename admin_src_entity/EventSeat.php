<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventSeat
 *
 * @ORM\Table(name="event_seat", indexes={@ORM\Index(name="FK_event_seat_eventid", columns={"event_id"})})
 * @ORM\Entity
 */
class EventSeat {

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
     * @ORM\Column(name="width", type="string", length=20, nullable=false)
     */
    private $width;

    /**
     * @var string
     *
     * @ORM\Column(name="height", type="string", length=20, nullable=false)
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column(name="pleft", type="string", length=20, nullable=false)
     */
    private $pleft;

    /**
     * @var string
     *
     * @ORM\Column(name="top", type="string", length=20, nullable=false)
     */
    private $top;

    /**
     * @var string
     *
     * @ORM\Column(name="lineheight", type="string", length=20, nullable=false)
     */
    private $lineheight;

    /**
     * @var string
     *
     * @ORM\Column(name="seat_entrance", type="string", length=200, nullable=false)
     */
    private $seatEntrance;

    /**
     * @var float
     *
     * @ORM\Column(name="seat_price", type="float", precision=10, scale=2, nullable=true)
     */
    private $seatPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=10, nullable=true)
     */
    private $currency;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_of_seats", type="integer", nullable=true)
     */
    private $numberOfSeats;

    /**
     * @var string
     *
     * @ORM\Column(name="ticket_type", type="string", length=200, nullable=true)
     */
    private $ticketType;

    /**
     * @var string
     *
     * @ORM\Column(name="redeem_on", type="string", length=200, nullable=true)
     */
    private $redeemOn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=false)
     */
    private $createdDate;

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
     * @var integer
     *
     * @ORM\Column(name="seatAvailability", type="smallint", nullable=false)
     */
    private $seatAvailability;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_deleted", type="smallint", nullable=false)
     */
    private $isDeleted;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set width
     *
     * @param string $width
     *
     * @return EventSeat
     */
    public function setWidth($width) {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return string
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param string $height
     *
     * @return EventSeat
     */
    public function setHeight($height) {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return string
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * Set pleft
     *
     * @param string $pleft
     *
     * @return EventSeat
     */
    public function setPleft($pleft) {
        $this->pleft = $pleft;

        return $this;
    }

    /**
     * Get pleft
     *
     * @return string
     */
    public function getPleft() {
        return $this->pleft;
    }

    /**
     * Set top
     *
     * @param string $top
     *
     * @return EventSeat
     */
    public function setTop($top) {
        $this->top = $top;

        return $this;
    }

    /**
     * Get top
     *
     * @return string
     */
    public function getTop() {
        return $this->top;
    }

    /**
     * Set lineheight
     *
     * @param string $lineheight
     *
     * @return EventSeat
     */
    public function setLineheight($lineheight) {
        $this->lineheight = $lineheight;

        return $this;
    }

    /**
     * Get lineheight
     *
     * @return string
     */
    public function getLineheight() {
        return $this->lineheight;
    }

    /**
     * Set seatEntrance
     *
     * @param string $seatEntrance
     *
     * @return EventSeat
     */
    public function setSeatEntrance($seatEntrance) {
        $this->seatEntrance = $seatEntrance;

        return $this;
    }

    /**
     * Get seatEntrance
     *
     * @return string
     */
    public function getSeatEntrance() {
        return $this->seatEntrance;
    }

    /**
     * Set seatPrice
     *
     * @param float $seatPrice
     *
     * @return EventSeat
     */
    public function setSeatPrice($seatPrice) {
        $this->seatPrice = $seatPrice;

        return $this;
    }

    /**
     * Get seatPrice
     *
     * @return float
     */
    public function getSeatPrice() {
        return $this->seatPrice;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return EventSeat
     */
    public function setCurrency($currency) {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * Set numberOfSeats
     *
     * @param integer $numberOfSeats
     *
     * @return EventSeat
     */
    public function setNumberOfSeats($numberOfSeats) {
        $this->numberOfSeats = $numberOfSeats;

        return $this;
    }

    /**
     * Get numberOfSeats
     *
     * @return integer
     */
    public function getNumberOfSeats() {
        return $this->numberOfSeats;
    }

    /**
     * Set ticketType
     *
     * @param string $ticketType
     *
     * @return EventSeat
     */
    public function setTicketType($ticketType) {
        $this->ticketType = $ticketType;

        return $this;
    }

    /**
     * Get ticketType
     *
     * @return string
     */
    public function getTicketType() {
        return $this->ticketType;
    }

    /**
     * Set redeemOn
     *
     * @param string $redeemOn
     *
     * @return EventSeat
     */
    public function setRedeemOn($redeemOn) {
        $this->redeemOn = $redeemOn;

        return $this;
    }

    /**
     * Get redeemOn
     *
     * @return string
     */
    public function getRedeemOn() {
        return $this->redeemOn;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return EventSeat
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
     * Set event
     *
     * @param \Admin\Entity\Event $event
     *
     * @return EventSeat
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

    /** Get IsDeleted
     * 
     * @return smallint
     * @author Aditya
     */
    public function getIsDeleted() {
        return $this->isDeleted;
    }

    /**
     * Set isDisabled
     * @param type $isDeleted
     * @return \Admin\Entity\EventSchedule
     * $author Aditya
     */
    public function setIsDeleted($isDeleted) {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    //Added by Yesh
    /** Get seatAvailability
     * 
     * @return smallint
     * @author Aditya
     */
    public function getSeatAvailability() {
        return $this->seatAvailability;
    }

    /**
     * Set seatAvailability
     * @param type $seatAvailability
     * @return \Admin\Entity\EventSchedule
     * $author Yeshan
     */
    public function setSeatAvailability($seatAvailability) {
        $this->seatAvailability = $seatAvailability;
        return $this;
    }

    //Added by Yesh
}
