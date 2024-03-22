<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * ReservationRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReservationRepository extends EntityRepository
{
    public function loadReservationsForPeriodForSingleAppartment($startDate, $period, \App\Entity\Appartment $appartment)
    {
        $start = date('Y-m-d', $startDate);
        $end = date('Y-m-d', $startDate + ($period * 3600 * 24));

        $q = $this
            ->createQueryBuilder('u')
            ->select('u')
            ->where('u.appartment = :app ')
            ->andWhere('((u.startDate >= :start AND u.endDate <= :end) OR'
                .'(u.startDate < :start AND u.endDate >= :start) OR'
                .'(u.startDate <= :end AND u.endDate > :end) OR'
                .'(u.startDate < :start AND u.endDate > :end))')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('app', $appartment->getId())
            ->addOrderBy('u.endDate', 'ASC')
            ->getQuery();

        $reservations = null;
        try {
            $reservations = $q->getResult();
        } catch (NoResultException $e) {
        }

        return $reservations;
    }

    public function loadReservationsForPeriod($startDate, $endDate)
    {
        $start = date('Y-m-d', strtotime($startDate));
        $end = date('Y-m-d', strtotime($endDate));

        //        if($customer == null) {
        $q = $this
            ->createQueryBuilder('u')
            ->select('u')
            ->andWhere('((u.startDate >= :start AND u.startDate < :end AND u.endDate > :start AND u.endDate <= :end) OR'
                .'(u.startDate <= :start AND u.endDate > :start AND u.endDate <= :end) OR'
                .'(u.startDate >= :start AND u.startDate < :end AND u.endDate > :end) OR'
                .'(u.startDate <= :start AND u.endDate >= :end))')
            // ->andWhere('u.invoice IS NULL')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->addOrderBy('u.endDate', 'ASC')
            ->getQuery();
        //        } else {
        //            $q = $this
        //                ->createQueryBuilder('u')
        //                ->select('u')
        //                ->andWhere('((u.startDate >= :start AND u.startDate < :end AND u.endDate > :start AND u.endDate <= :end) OR'
        //                         .  '(u.startDate <= :start AND u.endDate > :start AND u.endDate <= :end) OR'
        //                         .  '(u.startDate >= :start AND u.startDate < :end AND u.endDate > :end) OR'
        //                         .  '(u.startDate <= :start AND u.endDate >= :end))')
        //                ->andWhere('u.invoice IS NULL')
        //                ->andWhere('u.booker = :customer')
        //                ->setParameter('start', $start)
        //                ->setParameter('end', $end)
        //                ->setParameter('customer', $customer)
        //                ->addOrderBy('u.endDate', 'ASC')
        //                ->getQuery();
        //        }

        $reservations = null;
        try {
            $reservations = $q->getResult();
        } catch (NoResultException $e) {
        }

        return $reservations;
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
        || is_subclass_of($class, $this->getEntityName());
    }

    public function loadReservationsForPeriodForSingleAppartmentWithoutStartAndEndDate($startDate, $period, \App\Entity\Appartment $appartment)
    {
        $start = date('Y-m-d', $startDate);
        $end = date('Y-m-d', $startDate + ($period * 3600 * 24));

        $q = $this
            ->createQueryBuilder('u')
            ->select('u')
            ->where('u.appartment = :app ')
            ->andWhere('((u.startDate >= :start AND u.startDate < :end AND u.endDate > :start AND u.endDate <= :end) OR'
                .'(u.startDate <= :start AND u.endDate > :start AND u.endDate <= :end) OR'
                .'(u.startDate >= :start AND u.startDate < :end AND u.endDate > :end) OR'
                .'(u.startDate <= :start AND u.endDate >= :end))')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('app', $appartment->getId())
            ->addOrderBy('u.endDate', 'ASC')
            ->getQuery();

        $reservations = null;
        try {
            $reservations = $q->getResult();
        } catch (NoResultException $e) {
        }

        return $reservations;
    }

    public function loadReservationsWithoutInvoiceForCustomer(\App\Entity\Customer $customer)
    {
        $q = $this
            ->createQueryBuilder('u')
            ->select('u')
            ->where('u.booker = :booker')
            // ->andWhere('u.invoice IS NULL')
            ->setParameter('booker', $customer->getId())
            ->addOrderBy('u.endDate', 'ASC')
            ->getQuery();

        $reservations = null;
        try {
            $reservations = $q->getResult();
        } catch (NoResultException $e) {
        }

        return $reservations;
    }

    public function loadUtilizationForDay($day, $objectId)
    {
        if ('all' === $objectId) {
            $query = $this->createQueryBuilder('u')
            ->select('SUM(u.persons)')
            ->where(':day >= u.startDate and :day < u.endDate')
            // ->andWhere('u.status=1')
            // ->addGroupBy('u.persons')
            ->setParameter('day', $day)
            ->getQuery();
        } else {
            $query = $this->createQueryBuilder('u')
            ->select('SUM(u.persons)')
            ->where('a.object = :objId and :day >= u.startDate and :day < u.endDate')
            // ->andWhere('u.status=1')
            ->join('u.appartment', 'a')
            // ->addGroupBy('u.persons')
            ->setParameter('day', $day)
            ->setParameter('objId', $objectId)
            ->getQuery();
        }

        try {
            return $query->getSingleScalarResult();
        } catch (NoResultException $ex) {
            return 0;
        }
    }

    public function getMinEndDate()
    {
        $q = $this
            ->createQueryBuilder('r')
            ->select('MIN(r.endDate)')
            ->getQuery();

        return $q->getSingleScalarResult();
    }

    public function getMaxStartDate()
    {
        $q = $this
            ->createQueryBuilder('r')
            ->select('MAX(r.startDate)')
            ->getQuery();

        return $q->getSingleScalarResult();
    }

    public function loadReservationsForMonth($month, $year, $objectId)
    {
        $startTs = strtotime($year.'-'.$month.'-01');
        $endDate = new \DateTime($year.'-'.$month.'-'.date('t', $startTs));
        $start = date('Y-m-d', $startTs);
        $end = $endDate->format('Y-m-d');

        $q = $this
            ->createQueryBuilder('u')
            ->select('u')
            ->join('u.appartment', 'a')
            // ->where('u.status=1')
            ->where('((u.startDate >= :start AND u.startDate < :end AND u.endDate > :start AND u.endDate <= :end) OR'
                .'(u.startDate <= :start AND u.endDate > :start AND u.endDate <= :end) OR'
                .'(u.startDate >= :start AND u.startDate < :end AND u.endDate > :end) OR'
                .'(u.startDate <= :start AND u.endDate >= :end))')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->addOrderBy('u.endDate', 'ASC');

        if ('all' !== $objectId) {
            $q->andWhere('a.object = :objId')
              ->setParameter('objId', $objectId);
        }

        try {
            return $q->getQuery()->getResult();
        } catch (NoResultException $e) {
            return [];
        }
    }

    public function loadOriginStatisticForPeriod($start, $end, $objectId)
    {
        if ('all' === $objectId) {
            $query = $this->createQueryBuilder('u')
            ->select('ro.id, COUNT(u.id) as origins')
            ->join('u.reservationOrigin', 'ro')
            ->where('u.startDate >= :start and u.endDate <= :end')
            // ->andWhere('u.status=1')
            ->addGroupBy('u.reservationOrigin')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery();
        } else {
            $query = $this->createQueryBuilder('u')
            ->select('ro.id, COUNT(u.id) as origins')
            ->join('u.reservationOrigin', 'ro')
            ->where('a.object = :objId and u.startDate >= :start and u.endDate <= :end')
            // ->andWhere('u.status=1')
            ->join('u.appartment', 'a')
            ->addGroupBy('u.reservationOrigin')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('objId', $objectId)
            ->getQuery();
        }

        try {
            return $query->getArrayResult();
        } catch (NoResultException $ex) {
            return 0;
        }
    }
}
