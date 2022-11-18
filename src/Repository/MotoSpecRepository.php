<?php

namespace App\Repository;

use App\Entity\MotoSpec;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MotoSpec>
 *
 * @method MotoSpec|null find($id, $lockMode = null, $lockVersion = null)
 * @method MotoSpec|null findOneBy(array $criteria, array $orderBy = null)
 * @method MotoSpec[]    findAll()
 * @method MotoSpec[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MotoSpecRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MotoSpec::class);
    }

    public function save(MotoSpec $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MotoSpec $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllWithPagination($page, $limit){
        $qb = $this->createQueryBuilder('m')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        $query = $qb->getQuery();
        $query->setFetchMode(MotoSpec::class, "concession", \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER);
        return $query->getResult();
    }

//    /**
//     * @return MotoSpec[] Returns an array of MotoSpec objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MotoSpec
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


}
