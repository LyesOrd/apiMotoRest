<?php

namespace App\Repository;

use App\Entity\Concession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Concession>
 *
 * @method Concession|null find($id, $lockMode = null, $lockVersion = null)
 * @method Concession|null findOneBy(array $criteria, array $orderBy = null)
 * @method Concession[]    findAll()
 * @method Concession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConcessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Concession::class);
    }

    public function save(Concession $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Concession $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Cette méthode Retourne $limit concession à partir de la page $page. 
     *
     * @param integer $page
     * @param integer $limit
     * @return mixed
     */
    public function findAllWithPagination($page, $limit) {
        $qb = $this->createQueryBuilder('c')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Concession[] Returns an array of Concession objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Concession
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
