<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class ZoneSeatsRepository extends EntityRepository {

    public function deleteAllExistsData($eventId) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->delete('Admin\Entity\ZoneSeats', 'u');
        $query = $query->where('u.eventId= :event_id')->setParameter('event_id', $eventId);
        $query = $query->getQuery();
        $Result = $query->execute();
        return $Result;
    }

    public function getSeatStatus($zoneId, $scheduleId) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\ZoneSeats', 'u');
        $query = $query->select('u.seatLabel,u.seatAvailability,u.userId', 'u');
        $query = $query->where('u.zoneId = :zoneId')->setParameter('zoneId', $zoneId);
        $query = $query->andWhere('u.scheduleId = :scheduleId')->setParameter('scheduleId', $scheduleId);
        $query = $query->getQuery();
        $Result = $query->execute();
//        $Result = $query->getSingleResult();
        return $Result;
    }

    public function updateSelectedSeat($zoneId, $scheduleId, $seatLabel, $status, $bookingId, $userId) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->update('Admin\Entity\ZoneSeats', 'u');
        $query = $query->set('u.seatAvailability', $status);
        $query = $query->set('u.bookingId', $bookingId);
        $query = $query->set('u.userId', $userId);
        $query = $query->where('u.zoneId = :zoneId')->setParameter('zoneId', $zoneId);
        $query = $query->andWhere('u.scheduleId = :scheduleId')->setParameter('scheduleId', $scheduleId);
        $query = $query->andWhere('u.seatLabel = :seatLabel')->setParameter('seatLabel', $seatLabel);
        $query = $query->getQuery();
        //print_r(array(
            //'sql' => $query->getSql(),
            //'parameters' => $query->getParameters(),
        //));
        $Result = $query->execute();
        return $Result;
    }

    public function unselectZoneSeats($zoneID, $eventID, $scheduleID, $seatLabel) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->update('Admin\Entity\ZoneSeats', 'u');
        $query = $query->set('u.seatAvailability', 0);
        $query = $query->set('u.userId', 0);
        $query = $query->where('u.seatLabel = :seatLabel')->setParameter('seatLabel', $seatLabel);
        $query = $query->andWhere('u.zoneId = :zoneId')->setParameter('zoneId', $zoneID);
        $query = $query->andWhere('u.eventId = :eventId')->setParameter('eventId', $eventID);
        $query = $query->andWhere('u.scheduleId = :scheduleId')->setParameter('scheduleId', $scheduleID);
        $query = $query->andWhere('u.seatAvailability = :seatAvailability')->setParameter('seatAvailability', 2);
        $query = $query->getQuery();
        $Result = $query->execute();
        return $Result;
    }

}
