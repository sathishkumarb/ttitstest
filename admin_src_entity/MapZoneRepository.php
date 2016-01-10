<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class MapZoneRepository extends EntityRepository {

    public function deleteAllExistsData($eventId) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->delete('Admin\Entity\MapZone', 'u');
        $query = $query->where('u.eventId= :event_id')->setParameter('event_id', $eventId);
        $query = $query->getQuery();
        $Result = $query->execute();
        return $Result;
    }

    public function getZoneByEventId($eventId) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\MapZone', 'u');
        $query = $query->select('u.id,u.zoneTitle');
        $query = $query->where('u.eventId= :event_id')->setParameter('event_id', $eventId);
        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }

    public function getZoneByTitle($zoneTitle, $eventID) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\MapZone', 'u');
        $query = $query->select('u.id,u.zonePrice,u.zoneTitle');
        $query = $query->where('u.zoneTitle= :zoneTitle')->setParameter('zoneTitle', $zoneTitle);
        $query = $query->andWhere('u.eventId= :eventId')->setParameter('eventId', $eventID);
        $query = $query->getQuery();
        $Result = $query->getSingleResult();
        return $Result;
    }

}
