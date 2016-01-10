<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class EventRepository extends EntityRepository {

    /**
     * getEventsListingAtAdminForDataTable
     * @param mixed $sqlArr - Data received from datatable
     * @return mixed Data to be sent to Datatable in desired format
     * @author Manu Garg
     */
    public function getEventsListingAtAdminForDataTable($sqlArr) {

        $sEcho = $sqlArr['sEcho'];

        /* To fetch Total no of records */
        $Result = $this->getEventsListingForDT($sqlArr, 0);
        $totalRecordCount = count($Result);

        /* To fetch records with paging */
        $Result = $this->getEventsListingForDT($sqlArr, $limited = 1);
        $key = 0;
        $UserDataList['sEcho'] = $sEcho;
        $UserDataList['iTotalRecords'] = $totalRecordCount;
        $UserDataList['iTotalDisplayRecords'] = $totalRecordCount;

        foreach ($Result as $eventRow) {
            $id = $eventRow->getId();
            $eventStatus = $eventRow->getStatus();
            if ($eventRow->getStatus() == 1) {
                $status = '<button title="Click here to In-active this event" id="status_' . $id . '" onclick="activeInactiveEvent(' . $id . ',\'inactive\');" class="btn btn-xs btn-danger" type="button">Inactive</button>';
            } else {
                $status = '<button title="Click here to activate this event" id="status_' . $id . '" onclick="activeInactiveEvent(' . $id . ',\'active\');" class="btn btn-xs btn-success" type="button">Active</button>';
            }

            //echo $id;
            //echo "===";
            $showCancel = 0;
            if ($eventStatus != 3) {
                $eventSchedules = $eventRow->getEventSchedule();
                foreach ($eventSchedules as $eventSchedule) {

                    /* echo $eventSchedule->getEventDate()->format("Y-m-d");
                      echo "=";
                      echo $eventSchedule->getEventTime()->format("H:i:s");
                      echo "=="; */
                    if ($eventSchedule->getEventDate() >= date_create(date('Y-m-d'))) {
                        $showCancel = 1;
                        break;
                    }
                }
            }
            //echo $showCancel;
            //echo "<br/>";
            $deleteUrl = '<a id="del_' . $id . '" alt="Delete Event" title="Click here to Delete this Event" href="javascript:void(0);" onClick="deleteEvent(' . $id . ')"> <i class="icon-trash"></i> </a>';
            $editUrl = '<a id="edit_' . $id . '" alt="Edit Event" title="Click here to Edit this Event" href="javascript:void(0);" onClick="editEvent(' . $id . ')"><i class="icon-edit"></i></a>';
            $cancelUrl = '<a id="cancel_' . $id . '" class="btn btn-xs btn-warning" alt="Cancel Event" title="Click here to Cancel this Event" href="javascript:void(0);" onClick="cancelEvent(' . $id . ')">Cancel</a>';
            $layoutUrl = '<a id="layout_' . $id . '" class="btn btn-xs btn-primary" alt="Edit Layout" title="Click here to Edit this Event Layout" href="javascript:void(0);" onClick="layoutEvent(' . $id . ')">Layout</a>';

            $UserDataList['data'][$key][0] = $eventRow->getEventName();
            $UserDataList['data'][$key][1] = $eventRow->getEventArtist();
            $UserDataList['data'][$key][2] = $eventRow->getEventAddress();
            $UserDataList['data'][$key][3] = $eventRow->getCategory()->getCategoryName();
            if ($showCancel == 1) {
                $UserDataList['data'][$key][4] = $status . "&nbsp;&nbsp;" . $layoutUrl . "&nbsp;&nbsp;" . $cancelUrl . "&nbsp;&nbsp;" . $editUrl . "&nbsp;&nbsp;" . $deleteUrl;
            } else {
                if ($eventStatus == 3) {
                    $UserDataList['data'][$key][4] = "Event Cancelled";
                } else {
                    $UserDataList['data'][$key][4] = $status . "&nbsp;&nbsp;" . $layoutUrl . "&nbsp;&nbsp;" . $editUrl . "&nbsp;&nbsp;" . $deleteUrl;
                }
            }

            //$UserDataList['data'][$key][4]  =  $status."&nbsp;&nbsp;&nbsp;&nbsp;".$deleteUrl."&nbsp;&nbsp;&nbsp;&nbsp;".$orderURL;
            $key++;
        }
        if ($key == 0) {
            $UserDataList['data'] = '';
        }

        return $UserDataList;
    }

    /**
     * getEventsListingForDT
     * @param mixed $sqlArr - Parameters sent from Data table 
     * @param integer $limited - Parameter used for paging in datatable
     * @return Mixed Array of Events objects
     * @author Manu Garg
     */
    public function getEventsListingForDT($sqlArr, $limited = 0) {
        if ($limited == 1) {
            /* For Paging in DataTables */
            $columnArr = ['e.eventName', 'e.eventArtist', 'e.eventAddress', 'e.category'];
            $sortByColumnName = $columnArr[$sqlArr['sortcolumn']];

            $sortType = $sqlArr['sorttype'];
            $offSet = $sqlArr['iDisplayStart'];
            $limit = $sqlArr['limit'];

            $offSet = (int) $offSet;
            $limit = (int) $limit;

            $sortType = strtoupper($sortType);
            if ($sortType == "ASC") {
                $sortType = 'ASC';
            } else {
                $sortType = 'DESC';
            }
        }

        $searchKey = $sqlArr['searchKey'];

        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        $query = $query->from('Admin\Entity\Event', 'e');
        $query = $query->select('e');
        $query = $query->join('e.category', 'c');
        $query = $query->where("e.status != 2");
        $query = $query->andWhere("c.status != 2");

        if (trim($searchKey) != '') {
            $searchKey = str_replace("<br>", '', trim($searchKey));
            $query = $query->andWhere('e.eventName LIKE :searchterm OR e.eventAddress LIKE :searchterm OR e.eventArtist LIKE :searchterm OR c.categoryName LIKE :searchterm')
                    ->setParameter('searchterm', '%' . $searchKey . '%');
        }
        if ($limited == 1) {
            $query = $query->setMaxResults($limit);
            $query = $query->setFirstResult($offSet);
            $query = $query->orderBy($sortByColumnName, $sortType);
        }
        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }

    public function getEventsSearch($type, $searchKey) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        $query = $query->from('Admin\Entity\Event', 'e');
        $query = $query->select('e');
        $query = $query->join('e.category', 'c');
        $query = $query->join('e.eventOption', 'o');
        $query = $query->join('e.eventSeat', 'es');
        $query = $query->where("e.status = 1");
        $query = $query->andwhere("c.status = 1");
        $query = $query->andWhere("s.isDeleted = 0");
        $query = $query->andWhere("o.isDeleted = 0");
        $query = $query->andWhere("es.isDeleted = 0");
        if (trim($searchKey) != '' and $type == "event") {
            $searchKey = str_replace("<br>", '', trim($searchKey));
            $query = $query->andWhere('e.eventName LIKE :searchterm ')
                    ->setParameter('searchterm', '%' . $searchKey . '%');
            $query = $query->orderBy('e.eventArtist', "DESC");
        }
        if (trim($searchKey) != '' and $type == "artist") {
            $searchKey = str_replace("<br>", '', trim($searchKey));
            $query = $query->andWhere('e.eventArtist LIKE :searchterm ')
                    ->setParameter('searchterm', '%' . $searchKey . '%');
            $query = $query->orderBy('e.eventArtist', "DESC");
        }
        if (trim($searchKey) != '' and $type == "venue") {
            $searchKey = str_replace("<br>", '', trim($searchKey));
            $query = $query->andWhere('e.eventVenueTitle LIKE :searchterm ')
                    ->setParameter('searchterm', '%' . $searchKey . '%');
            $query = $query->orderBy('e.eventVenueTitle', "DESC");
        }
        $query = $query->setMaxResults(2);
        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }

    /**
     * getFeaturedEvent - Function to fetch featured Event
     * @author Manu Garg
     * @return \Admin\Entity\Event -Array of Event 
     */
    public function getFeaturedEvent() {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        $query = $query->from('Admin\Entity\Event', 'e');
        $query = $query->select('e');
        $query = $query->join('e.eventSchedule', 's');
        $query = $query->join('e.category', 'c');
        $query = $query->join('e.eventOption', 'o');
        $query = $query->where("e.status = 1");
        $query = $query->andWhere("e.featured = 1");
        $query = $query->andwhere("c.status = 1");
        $query = $query->andWhere("s.isDeleted = 0");
        $query = $query->andWhere("o.isDeleted = 0");
        $query = $query->andWhere("s.eventDate >= '" . date('Y-m-d') . "'");

        $query = $query->getQuery();
        //echo $query->getSQL();
        $Result = $query->getResult();

        return $Result;
    }

    /**
     * getSearchEvent - Fetch Events on basis of City or category 
     * @param integer $city
     * @param integer $categoryId
     * @return \Admin\Entity\Event - Array of events
     * @author Aditya
     */
    public function getSearchEvent($type = null, $val = null, $offset = null, $limit = null) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        $query = $query->from('Admin\Entity\Event', 'e');
        $query = $query->distinct();
        $query = $query->select('e');
        $query = $query->join('e.eventSchedule', 's');
        $query = $query->join('e.category', 'c');
        $query = $query->join('e.eventCity', 'cities');
        $query = $query->join('e.eventOption', 'o');
//        $query = $query->join('e.eventSeat', 'es');
        $query = $query->where("e.status = 1");
        $query = $query->andwhere("c.status = 1");
        $query = $query->andWhere("s.isDeleted = 0");
        $query = $query->andWhere("o.isDeleted = 0");
//        $query = $query->andWhere("es.isDeleted = 0");
        $query = $query->andWhere("s.eventDate >= '" . date('Y-m-d') . "'");

        switch ($type) {
            case 'city':
                $query = $query->andWhere("cities.id = $val");
                break;
            case 'category':
                $query = $query->andWhere("c.id = '" . $val . "'");
                break;
            case 'title':
                $query = $query->andWhere("e.eventName = :val")->setParameter("val", $val);
                break;
            case 'venue':
                $query = $query->andWhere("e.eventVenueTitle = :val")->setParameter("val", $val);
                break;
            case 'artist':
                $query = $query->andWhere("e.eventArtist = :val")->setParameter("val", $val);
                break;
            case 'results':
                $query = $query->andWhere("(e.eventVenueTitle LIKE :val1 OR e.eventVenueTitle LIKE :val2) OR (e.eventArtist LIKE :val3 OR e.eventArtist LIKE :val4) OR (e.eventName LIKE :val5 or e.eventName LIKE :val6)")->setParameter("val1", $val . '%')->setParameter("val2", '% ' . $val . '%')->setParameter("val3", $val . '%')->setParameter("val4", '% ' . $val . '%')->setParameter("val5", $val . '%')->setParameter("val6", '% ' . $val . '%');
                break;
        }
        //$query = $query->andWhere("e.status = 1");
        //$query = $query->andWhere("s.eventDate >= '".date('Y-m-d')."'");
        //$query = $query->andWhere("s.isDeleted = 0");   
        if ($offset != null) {
            $query = $query->setFirstResult($offset);
        }
        if ($limit != null) {
            $query = $query->setMaxResults($limit);
        }
        $query = $query->getQuery();
//        echo $query->getSQL();
//        $parameters = $query->getParameters();
//        print_r($parameters);
//        die;
        $Result = $query->getResult();
        return $Result;
    }

    public function getAllEvents() {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        /* $query = $query->from('Admin\Entity\Event', 'e');
          $query = $query->select('e');
          $query = $query->join('e.eventSchedule','s');
          $query = $query->join('e.category','c');
          $query = $query->Where("c.status = 1");
          $query = $query->andWhere("e.status = 1");
          $query = $query->andWhere("s.eventDate >= '".date('Y-m-d')."'");
          $query = $query->andWhere("s.isDeleted = 0"); */

        $query = $query->from('Admin\Entity\Event', 'e');
        $query = $query->select('e');
        $query = $query->join('e.category', 'c');        /* Event Category Join */
        $query = $query->join('e.eventSchedule', 's');   /* Event Schedule Join */
        $query = $query->join('e.eventOption', 'o');     /* Event Option Join */
//        $query = $query->join('e.eventSeat', 'es');     /* Event Seat Join */
        $query = $query->where("e.status = 1");
        $query = $query->andWhere("c.status = 1");
        $query = $query->andWhere("s.eventDate >= '" . date('Y-m-d') . "'");
//        $query = $query->andWhere("es.isDeleted = 0");
        $query = $query->andWhere("s.isDeleted = 0");
        $query = $query->andWhere("o.isDeleted = 0");

        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }

    /**
     * getHomePageEvents - Function to fetch Home Page Event
     * @author Manu Garg
     * @return \Admin\Entity\Event -Array of Event 
     */
    public function getHomePageEvents($cityobj = "") {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        $query = $query->from('Admin\Entity\Event', 'e');
        $query = $query->select('e');
        $query = $query->join('e.category', 'c');
        $query = $query->join('e.eventSchedule', 's');
        $query = $query->where("e.status = 1");
        $query = $query->andWhere("c.status = 1");
        if (!empty($cityobj)) {
            $query = $query->andWhere("e.eventCity = " . $cityobj->getId());
        }
        $query = $query->andWhere("s.eventDate >= '" . date('Y-m-d') . "'");
        $query = $query->andWhere("s.isDeleted = 0");
        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }

    public function getEvent($eventId) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        $query = $query->from('Admin\Entity\Event', 'e');
        $query = $query->select('e');
        $query = $query->join('e.category', 'c');        /* Event Category Join */
        $query = $query->join('e.eventSchedule', 's');   /* Event Schedule Join */
        $query = $query->join('e.eventOption', 'o');     /* Event Option Join */
        $query = $query->where("e.status = 1");
        $query = $query->andWhere("c.status = 1");
        $query = $query->andWhere("s.eventDate >= '" . date('Y-m-d') . "'");
        $query = $query->andWhere("s.isDeleted = 0");
        $query = $query->andWhere("o.isDeleted = 0");
        $query = $query->andWhere("e.id = :eventId")->setParameter('eventId', $eventId);
        $query = $query->getQuery();
        //$Result = $query->getResult();
        //echo $query->getSQL();die;
        try {
            $Result = $query->getSingleResult();
        } catch (\Exception $e) {
            $Result = array();
        }
        return $Result;
    }

    //Added by Yesh
    public function getEventList() {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        $query = $query->from('Admin\Entity\Event', 'e');
        $query = $query->select('e.id,e.eventName');
        $query = $query->where('e.status = 1');
        $query = $query->getQuery();
        $Result = $query->execute();
        return $Result;
    }

}
