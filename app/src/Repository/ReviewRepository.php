<?php

namespace App\Repository;

use App\Entity\Hotel;
use App\Entity\Review;
use Carbon\CarbonPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Review $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Review $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getAvgScore(Hotel $hotel, int $dateRange): float
    {
        $qb = $this->createQueryBuilder('r');

        $res = $qb->select('max(r.createdDate) as max, min(r.createdDate) as min')
            ->andWhere('r.hotel = :hotel')
            ->setParameter('hotel', $hotel)
            ->orderBy('r.createdDate', 'desc')
            ->getQuery()
            ->getSingleResult();

        $periods = CarbonPeriod::since($res['min'])->days($dateRange)->until($res['max']);

        $availableDates = $periods->toArray();
        $iMax = count($availableDates);

        $aggrResults = $qb->select('avg(r.score) as eachDayAverage, r.createdDate')
            ->andWhere('r.hotel = :hotel')
            ->setParameter('hotel', $hotel)
            ->groupBy('r.createdDate')
            ->getQuery()
            ->getArrayResult();

        $totalAggrScoreSum = array_reduce($aggrResults, function ($carry, $item){
            $carry += $item['eachDayAverage'];
            return $carry;
        });

        $totalAggrScoreSum /= $dateRange;

        return $totalAggrScoreSum / $iMax;
    }
}
