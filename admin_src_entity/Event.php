<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event", indexes={@ORM\Index(name="FK_event_categoryId", columns={"category_id"}), @ORM\Index(name="FK_event_country", columns={"event_country"}), @ORM\Index(name="FK_event_city", columns={"event_city"}), @ORM\Index(name="FK_event_layout", columns={"layout_id"})})
 * @ORM\Entity(repositoryClass="EventRepository")
 */
class Event {

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
     * @ORM\Column(name="event_name", type="string", length=200, nullable=false)
     */
    private $eventName;

    /**
     *
     * @var type string
     * @ORM\Column(name="perf_code", type="string", length=75, nullable=false)
     */
    private $perfCode;

    /**
     * @var string
     *
     * @ORM\Column(name="event_desc", type="text", length=65535, nullable=false)
     */
    private $eventDesc;

    /**
     * @var string
     *
     * @ORM\Column(name="event_address", type="string", length=200, nullable=false)
     */
    private $eventAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="event_zip", type="string", length=10, nullable=true)
     */
    private $eventZip;

    /**
     * @var string
     *
     * @ORM\Column(name="event_venue_title", type="string", length=100, nullable=true)
     */
    private $eventVenueTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="event_venue_icon", type="string", length=100, nullable=true)
     */
    private $eventVenueIcon;

    /**
     * @var string
     *
     * @ORM\Column(name="event_image_big", type="string", length=200, nullable=true)
     */
    private $eventImageBig;

    /**
     * @var string
     *
     * @ORM\Column(name="event_image_medium", type="string", length=200, nullable=true)
     */
    private $eventImageMedium;

    /**
     * @var string
     *
     * @ORM\Column(name="event_image_small", type="string", length=200, nullable=true)
     */
    private $eventImageSmall;

    /**
     * @var string
     *
     * @ORM\Column(name="event_image_banner", type="string", length=200, nullable=true)
     */
    private $eventImageBanner;

    /**
     * @var string
     *
     * @ORM\Column(name="event_artist", type="string", length=200, nullable=false)
     */
    private $eventArtist;

    /**
     * @var string
     *
     * @ORM\Column(name="event_link", type="string", length=200, nullable=true)
     */
    private $eventLink;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=20, nullable=true)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=20, nullable=true)
     */
    private $latitude;

    /**
     * @var integer
     *
     * @ORM\Column(name="featured", type="smallint", nullable=true)
     */
    private $featured = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified_date", type="datetime", nullable=true)
     */
    private $modifiedDate;

    /**
     * @var \Admin\Entity\Categories
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\Categories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * })
     */
    private $category;

    /**
     * @var \Admin\Entity\City
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_city", referencedColumnName="id")
     * })
     */
    private $eventCity;

    /**
     * @var \Admin\Entity\Countries
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\Countries")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_country", referencedColumnName="id")
     * })
     */
    private $eventCountry;

    /**
     * @var \Admin\Entity\Layout
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\Layout")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="layout_id", referencedColumnName="id")
     * })
     */
    private $layout;

    /**
     * @var \Admin\Entity\EventSchedule
     * 
     * @ORM\OneToMany(targetEntity="Admin\Entity\EventSchedule", mappedBy="event")
     * @ORM\JoinColumn(name="id", referencedColumnName="event_id")
     * @ORM\OrderBy({"eventDate" = "ASC","eventTime" = "ASC"})
     */
    private $eventSchedule;

    /**
     * @var \Admin\Entity\EventSeat
     * 
     * @ORM\OneToMany(targetEntity="Admin\Entity\EventSeat", mappedBy="event")
     * @ORM\JoinColumn(name="id", referencedColumnName="event_id")
     */
    private $eventSeat;

    /**
     * @var \Admin\Entity\EventOption
     * 
     * @ORM\OneToMany(targetEntity="Admin\Entity\EventOption", mappedBy="event")
     * @ORM\JoinColumn(name="id", referencedColumnName="event_id")
     */
    private $eventOption;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set eventName
     *
     * @param string $eventName
     *
     * @return Event
     */
    public function setEventName($eventName) {
        $this->eventName = $eventName;

        return $this;
    }

    /**
     * get Perf Code
     * @return type string 
     */
    public function getPerfCode() {
        return $this->perfCode;
    }

    /**
     * set Perf Code
     * @param type $perfCode
     * @return \Admin\Entity\Event
     */
    public function setPerfCode($perfCode) {
        $this->perfCode = $perfCode;
        return $this;
    }

    /**
     * Get eventName
     *
     * @return string
     */
    public function getEventName() {
        return $this->eventName;
    }

    /**
     * Set eventDesc
     *
     * @param string $eventDesc
     *
     * @return Event
     */
    public function setEventDesc($eventDesc) {
        $this->eventDesc = $eventDesc;
        return $this;
    }

    /**
     * Get eventDesc
     *
     * @return string
     */
    public function getEventDesc() {
        return $this->eventDesc;
    }

    /**
     * Set eventAddress
     *
     * @param string $eventAddress
     *
     * @return Event
     */
    public function setEventAddress($eventAddress) {
        $this->eventAddress = $eventAddress;

        return $this;
    }

    /**
     * Get eventAddress
     *
     * @return string
     */
    public function getEventAddress() {
        return $this->eventAddress;
    }

    /**
     * Set eventZip
     *
     * @param string $eventZip
     *
     * @return Event
     */
    public function setEventZip($eventZip) {
        $this->eventZip = $eventZip;

        return $this;
    }

    /**
     * Get eventZip
     *
     * @return string
     */
    public function getEventZip() {
        return $this->eventZip;
    }

    /**
     * Set eventVenueTitle
     *
     * @param string $eventVenueTitle
     *
     * @return Event
     */
    public function setEventVenueTitle($eventVenueTitle) {
        $this->eventVenueTitle = $eventVenueTitle;

        return $this;
    }

    /**
     * Get eventVenueTitle
     *
     * @return string
     */
    public function getEventVenueTitle() {
        return $this->eventVenueTitle;
    }

    /**
     * Set eventVenueIcon
     *
     * @param string $eventVenueIcon
     *
     * @return Event
     */
    public function setEventVenueIcon($eventVenueIcon) {
        $this->eventVenueIcon = $eventVenueIcon;

        return $this;
    }

    /**
     * Get eventVenueIcon
     *
     * @return string
     */
    public function getEventVenueIcon() {
        return $this->eventVenueIcon;
    }

    /**
     * Set eventImageBig
     *
     * @param string $eventImageBig
     *
     * @return Event
     */
    public function setEventImageBig($eventImageBig) {
        $this->eventImageBig = $eventImageBig;

        return $this;
    }

    /**
     * Get eventImageBig
     *
     * @return string
     */
    public function getEventImageBig() {
        return $this->eventImageBig;
    }

    /**
     * Set eventImageMedium
     *
     * @param string $eventImageMedium
     *
     * @return Event
     */
    public function setEventImageMedium($eventImageMedium) {
        $this->eventImageMedium = $eventImageMedium;

        return $this;
    }

    /**
     * Get eventImageMedium
     *
     * @return string
     */
    public function getEventImageMedium() {
        return $this->eventImageMedium;
    }

    /**
     * Set eventImageSmall
     *
     * @param string $eventImageSmall
     *
     * @return Event
     */
    public function setEventImageSmall($eventImageSmall) {
        $this->eventImageSmall = $eventImageSmall;

        return $this;
    }

    /**
     * Get eventImageSmall
     *
     * @return string
     */
    public function getEventImageSmall() {
        return $this->eventImageSmall;
    }

    /**
     * Set eventImageBanner
     *
     * @param string $eventImageBanner
     *
     * @return Event
     */
    public function setEventImageBanner($eventImageBanner) {
        $this->eventImageBanner = $eventImageBanner;

        return $this;
    }

    /**
     * Get eventImageBanner
     *
     * @return string
     */
    public function getEventImageBanner() {
        return $this->eventImageBanner;
    }

    /**
     * Set eventArtist
     *
     * @param string $eventArtist
     *
     * @return Event
     */
    public function setEventArtist($eventArtist) {
        $this->eventArtist = $eventArtist;

        return $this;
    }

    /**
     * Get eventArtist
     *
     * @return string
     */
    public function getEventArtist() {
        return $this->eventArtist;
    }

    /**
     * Set eventLink
     *
     * @param string $eventLink
     *
     * @return Event
     */
    public function setEventLink($eventLink) {
        $this->eventLink = $eventLink;

        return $this;
    }

    /**
     * Get eventLink
     *
     * @return string
     */
    public function getEventLink() {
        return $this->eventLink;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Event
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
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Event
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
     * Set featured
     *
     * @param integer $featured
     *
     * @return Event
     */
    public function setFeatured($featured) {
        $this->featured = $featured;

        return $this;
    }

    /**
     * Get featured
     *
     * @return integer
     */
    public function getFeatured() {
        return $this->featured;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Event
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
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Event
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
     * Set modifiedDate
     *
     * @param \DateTime $modifiedDate
     *
     * @return Event
     */
    public function setModifiedDate($modifiedDate) {
        $this->modifiedDate = $modifiedDate;

        return $this;
    }

    /**
     * Get modifiedDate
     *
     * @return \DateTime
     */
    public function getModifiedDate() {
        return $this->modifiedDate;
    }

    /**
     * Set category
     *
     * @param \Admin\Entity\Categories $category
     *
     * @return Event
     */
    public function setCategory(\Admin\Entity\Categories $category = null) {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Admin\Entity\Categories
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * Set eventCity
     *
     * @param \Admin\Entity\City $eventCity
     *
     * @return Event
     */
    public function setEventCity(\Admin\Entity\City $eventCity = null) {
        $this->eventCity = $eventCity;

        return $this;
    }

    /**
     * Get eventCity
     *
     * @return \Admin\Entity\City
     */
    public function getEventCity() {
        return $this->eventCity;
    }

    /**
     * Set eventCountry
     *
     * @param \Admin\Entity\Countries $eventCountry
     *
     * @return Event
     */
    public function setEventCountry(\Admin\Entity\Countries $eventCountry = null) {
        $this->eventCountry = $eventCountry;

        return $this;
    }

    /**
     * Get eventCountry
     *
     * @return \Admin\Entity\Countries
     */
    public function getEventCountry() {
        return $this->eventCountry;
    }

    /**
     * Set layout
     *
     * @param \Admin\Entity\Layout $layout
     *
     * @return Event
     */
    public function setLayout(\Admin\Entity\Layout $layout = null) {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Get layout
     *
     * @return \Admin\Entity\Layout
     */
    public function getLayout() {
        return $this->layout;
    }

    /**
     * Get eventSchedule
     *
     * @return \Admin\Entity\EventSchedule
     */
    public function getEventSchedule() {
        return $this->eventSchedule;
    }

    /**
     * Get eventOption
     *
     * @return \Admin\Entity\EventOption
     */
    public function getEventOption() {
        return $this->eventOption;
    }

    /**
     * Get eventSeat
     *
     * @return \Admin\Entity\EventSeat
     */
    public function getEventSeat() {
        return $this->eventSeat;
    }

}
