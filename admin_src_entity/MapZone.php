<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MapZone
 * Added by Yesh
 * @ORM\Table(name="map_zone")
 * @ORM\Entity(repositoryClass="MapZoneRepository")
 */
class MapZone {

    /**
     * @var integer
     *  
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     *
     * @var integer
     * @ORM\Column(name="event_id", type="integer", nullable=false)
     */
    private $eventId;

    /**
     * @var integer
     * @ORM\Column(name="map_id", type="integer", nullable=false)
     */
    private $mapId;

    /**
     * @var string
     * @ORM\Column(name="zone_title", type="string", nullable=false)
     */
    private $zoneTitle;

    /**
     *
     * @var type string 
     * @ORM\Column(name="zone_dtcm", type="string", nullable=false)
     */
    private $zoneDtcm;

    /**
     * @var flot
     * @ORM\Column(name="zone_price", type="float", nullable=false)
     */
    private $zonePrice;

    /**
     * @var string 
     * @ORM\Column(name="zone_count", type="string", nullable=false)
     */
    private $zoneCount;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
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
     * @return \Admin\Entity\MapZone
     */
    public function setEventId($eventId) {
        $this->eventId = $eventId;
        return $this;
    }

    /**
     * get MapId
     * @return integer
     */
    public function getMapId() {
        return $this->mapId;
    }

    /**
     * set MapId
     * @param type $mapId
     * @return \Admin\Entity\MapZone
     */
    public function setMapId($mapId) {
        $this->mapId = $mapId;
        return $this;
    }

    /**
     * get ZoneTitle
     * @return string
     */
    public function getZoneTitle() {
        return $this->zoneTitle;
    }

    /**
     * set ZoneTitle
     * @param type $zoneTitle
     * @return \Admin\Entity\MapZone
     */
    public function setZoneTitle($zoneTitle) {
        $this->zoneTitle = $zoneTitle;
        return $this;
    }

    /**
     * get Zone Dtcm
     * @return type string 
     */
    public function getZoneDtcm() {
        return $this->zoneDtcm;
    }

    /**
     * set Zone Dtcm
     * @param type $zoneDtcm
     * @return \Admin\Entity\MapZone
     */
    public function setZoneDtcm($zoneDtcm) {
        $this->zoneDtcm = $zoneDtcm;
        return $this;
    }

    /**
     * get ZonePrice
     * @return float
     */
    public function getZonePrice() {
        return $this->zonePrice;
    }

    /**
     * set ZonePrice
     * @param type $zonePrice
     * @return \Admin\Entity\MapZone
     */
    public function setZonePrice($zonePrice) {
        $this->zonePrice = $zonePrice;
        return $this;
    }

    /**
     * get ZoneCount
     * @return string
     */
    public function getZoneCount() {
        return $this->zoneCount;
    }

    /**
     * set ZoneCount
     * @param type $zoneCount
     * @return \Admin\Entity\MapZone
     */
    public function setZoneCount($zoneCount) {
        $this->zoneCount = $zoneCount;
        return $this;
    }

}
