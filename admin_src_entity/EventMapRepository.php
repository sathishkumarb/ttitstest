<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class EventMapRepository extends EntityRepository {

    public function getCountByEventId($eventId) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\EventMap', 'u');
        $query = $query->select('count(u.id)');
        $query = $query->where('u.eventId= :event_id')->setParameter('event_id', $eventId);
        $Result = $query->getQuery()->getSingleScalarResult();
        return $Result;
    }

    public function getMapByEventId($eventId) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\EventMap', 'u');
        $query = $query->select('u.id,u.eventId,u.mapObject');
        $query = $query->where('u.eventId= :event_id')->setParameter('event_id', $eventId);
        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }

    public function deleteAllExistsData($eventId) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->delete('Admin\Entity\EventMap', 'u');
        $query = $query->where('u.eventId= :event_id')->setParameter('event_id', $eventId);
        $query = $query->getQuery();
        $Result = $query->execute();
        return $Result;
    }

}
