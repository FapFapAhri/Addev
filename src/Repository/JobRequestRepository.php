<?php

namespace App\Repository;

use App\Entity\JobRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobRequest[]    findAll()
 * @method JobRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JobRequest::class);
    }

//    /**
//     * @return JobRequest[] Returns an array of JobRequest objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JobRequest
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
