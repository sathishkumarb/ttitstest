<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZoneSeats
 * Added by Yesh
 * @ORM\Table(name="zone_seats")
 * @ORM\Entity(repositoryClass="ZoneSeatsRepository")
 */
class ZoneSeats {

    /**
     * @var integer
     *  
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     * 
     * @ORM\Column(name="schedule_id", type="integer", nullable=false)
     */
    private $scheduleId;

    /**
     *
     * @var integer
     * @ORM\Column(name="event_id", type="integer", nullable=false)
     */
    private $eventId;

    /**
     *
     * @var integer
     * @ORM\Column(name="zone_id", type="integer", nullable=false)
     */
    private $zoneId;

    /**
     * @var string
     * @ORM\Column(name="seat_label", type="string", nullable=false)
     */
    private $seatLabel;

    /**
     *
     * @var type string
     * @ORM\Column(name="row_id", type="string", nullable=false)
     */
    private $rowId;

    /**
     *
     * @var type string
     * @ORM\Column(name="col_id", type="string", nullable=false)
     */
    private $colId;

    /**
     *
     * @var smallint 
     * @ORM\Column(name="seat_availability", type="smallint", nullable=false)
     */
    private $seatAvailability;

    /**
     *
     * @var type bigint
     * @ORM\Column(name="booking_id", type="bigint", nullable=true)
     */
    private $bookingId;

    /**
     *
     * @var type integer
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $userId;

    /**
     *
     * @var type string
     * @ORM\Column(name="barcode", type="string", nullable=true)
     */
    private $barcode;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * get ScheduleId
     * @return integer
     */
    public function getScheduleId() {
        return $this->scheduleId;
    }

    /**
     * set ScheduleId
     * @param type $scheduleId
     * @return \Admin\Entity\ZoneSeats
     */
    public function setScheduleId($scheduleId) {
        $this->scheduleId = $scheduleId;
        return $this;
    }

    /**
     * get EventId
     * @return integer
     */
    public function getEventId() {
        return $this->eventId;
    }

    /**
     * set EventId
     * @param type $eventId
     * @return \Admin\Entity\ZoneSeats
     */
    public function setEventId($eventId) {
        $this->eventId = $eventId;
        return $this;
    }

    /**
     * get ZoneId
     * @return integer
     */
    public function getZoneId() {
        return $this->zoneId;
    }

    /**
     * set ZoneId
     * @param type $zoneId
     * @return \Admin\Entity\ZoneSeats
     */
    public function setZoneId($zoneId) {
        $this->zoneId = $zoneId;
        return $this;
    }

    /**
     * get SeatLabel
     * @return string
     */
    public function getSeatLabel() {
        return $this->seatLabel;
    }

    /**
     * set SeatLabel
     * @param type $seatLabel
     * @return \Admin\Entity\ZoneSeats
     */
    public function setSeatLabel($seatLabel) {
        $this->seatLabel = $seatLabel;
        return $this;
    }

    /**
     * get Row Id
     * @return type string
     */
    public function getRowId() {
        return $this->rowId;
    }

    /**
     * set Row Id
     * @param type $rowId
     * @return \Admin\Entity\ZoneSeats
     */
    public function setRowId($rowId) {
        $this->rowId = $rowId;
        return $this;
    }

    /**
     * get Col Id
     * @return type string 
     */
    public function getColId() {
        return $this->colId;
    }

    /**
     * set Col Id
     * @param type $colId
     * @return \Admin\Entity\ZoneSeats
     */
    public function setColId($colId) {
        $this->colId = $colId;
        return $this;
    }

    /**
     * get SeatAvailability
     * @return smallint
     */
    public function getSeatAvailability() {
        return $this->seatAvailability;
    }

    /**
     * set SeatAvailability
     * @param type $seatAvailability
     * @return \Admin\Entity\ZoneSeats
     */
    public function setSeatAvailability($seatAvailability) {
        $this->seatAvailability = $seatAvailability;
        return $this;
    }

    /**
     * get BookingId
     * @return type biginit
     */
    public function getBookingId() {
        return $this->bookingId;
    }

    /**
     * set BookingId
     * @param type $bookingId
     * @return \Admin\Entity\ZoneSeats
     */
    public function setBookingId($bookingId) {
        $this->bookingId = $bookingId;
        return $this;
    }

    /**
     * get UserId
     * @return type integer
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * set UserId
     * @param type $userId
     * @return \Admin\Entity\ZoneSeats
     */
    public function setUserId($userId) {
        $this->userId = $userId;
        return $this;
    }

    /**
     * get Barcode
     * @return type string
     */
    public function getBarcode() {
        return $this->barcode;
    }

    /**
     * set Barcode
     * @param type $barcode
     * @return \Admin\Entity\ZoneSeats
     */
    public function setBarcode($barcode) {
        $this->barcode = $barcode;
        return $this;
    }

}
