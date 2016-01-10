<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class EventScheduleRepository extends EntityRepository {

    public function getSchedulesByEventID($event_id) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\EventSchedule', 'u');
        $query = $query->select('u.id');
        $query = $query->where('u.eventId= :event_id')->setParameter('event_id', $event_id);
        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }

    public function getEventScheduleIdByEventDate($eventID, $eventDate) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\EventSchedule', 'u');
        $query = $query->select('u.id');
        $query = $query->where('u.eventId= :event_id')->setParameter('event_id', $eventID);
        $query = $query->andWhere('u.eventDate= :event_date')->setParameter('event_date', $eventDate);
        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }

}
