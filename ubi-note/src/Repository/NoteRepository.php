<?php

namespace App\Repository;

use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Note|null find($id, $lockMode = null, $lockVersion = null)
 * @method Note|null findOneBy(array $criteria, array $orderBy = null)
 * @method Note[]    findAll()
 * @method Note[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NoteRepository
    extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }
    
    /**
     * @param int $id
     *
     * @return int|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMoyenneEleve(int $id): ?int
    {
        $connection = $this->getEntityManager()
                           ->getConnection();
        
        $sql       = 'SELECT AVG(n.note) as moy , count(*) as cnt FROM note as n
                      WHERE n.eleve_id = :id';
        $statement = $connection->prepare($sql);
        $statement->execute(['id' => $id]);
        
        $res = (object)$statement->fetch();
        if ($res->cnt == 0) {
            return null;
        }
        
        return round($res->moy, 2);
    }
    
    /**     *
     * @return int|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMoyenneGenerale(): ?int
    {
        $connection = $this->getEntityManager()
                           ->getConnection();
        
        $sql       = 'SELECT AVG(n.note) as moy , count(*) as cnt FROM note as n';
        $statement = $connection->prepare($sql);
        $statement->execute();
        
        $res = (object)$statement->fetch();
        if ($res->cnt == 0) {
            return null;
        }
        
        return round($res->moy, 2);
    }
    
    
    
    
    // /**
    //  * @return Note[] Returns an array of Note objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    
    /*
    public function findOneBySomeField($value): ?Note
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
