<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventMap
 * Added by Yesh
 * @ORM\Table(name="event_map")
 * @ORM\Entity(repositoryClass="EventMapRepository")
 */
class EventMap {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var interger
     * @ORM\Column(name="event_id", type="integer", nullable=false)
     */
    private $eventId;

    /**
     * @var string 
     * @ORM\Column(name="map_object", type="text", nullable=false)
     */
    private $mapObject;

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
     * @return interger
     */
    public function getEventId() {
        return $this->eventId;
    }

    /**
     * 
     * @param type $eventId
     * @return \Admin\Entity\EventMap
     */
    public function setEventId($eventId) {
        $this->eventId = $eventId;
        return $this;
    }

    /**
     * get MapObject
     * @return string
     */
    public function getMapObject() {
        return $this->mapObject;
    }

    /**
     * 
     * @param type $mapObject
     * @return \Admin\Entity\EventMap
     */
    public function setMapObject($mapObject) {
        $this->mapObject = $mapObject;
        return $this;
    }

}
