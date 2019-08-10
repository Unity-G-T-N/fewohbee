<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * InvoiceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InvoiceRepository extends EntityRepository
{
    public function getLastInvoiceId()
    {
        $q = $this
            ->createQueryBuilder('i')
            ->select('i.number')
            ->where("i.id=(SELECT MAX(i2.id) FROM App\Entity\Invoice i2)")
            ->getQuery();

        try {
            return $q->getSingleScalarResult();
        } catch (NoResultException $ex) {
            return "";
        }
    }

    public function findByFilter($search, $page = 1, $limit = 20)
    {
        $q = $this
            ->createQueryBuilder('i')
            ->select('i')
            ->where('i.number LIKE :search or i.firstname LIKE :search or i.lastname LIKE :search or i.company LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->addOrderBy('i.date', 'DESC')
            ->addOrderBy('i.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();
        $paginator = new Paginator($q, $fetchJoinCollection = false);

        return $paginator;
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
        || is_subclass_of($class, $this->getEntityName());
    }
}
