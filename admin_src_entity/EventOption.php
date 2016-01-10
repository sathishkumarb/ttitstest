<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventOption
 *
 * @ORM\Table(name="event_option", indexes={@ORM\Index(name="FK_event_option_eventid", columns={"event_id"}), @ORM\Index(name="FK_event_option_optionid", columns={"option_id"})})
 * @ORM\Entity
 */
class EventOption {

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
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var \Admin\Entity\MainOptions
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\MainOptions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="option_id", referencedColumnName="id")
     * })
     */
    private $option;

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
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return EventOption
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
     * Set option
     *
     * @param \Admin\Entity\MainOptions $option
     *
     * @return EventOption
     */
    public function setOption(\Admin\Entity\MainOptions $option = null) {
        $this->option = $option;

        return $this;
    }

    /**
     * Get option
     *
     * @return \Admin\Entity\MainOptions
     */
    public function getOption() {
        return $this->option;
    }

    /**
     * Set event
     *
     * @param \Admin\Entity\Event $event
     *
     * @return EventOption
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

}
