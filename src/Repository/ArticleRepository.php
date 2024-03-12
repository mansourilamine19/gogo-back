<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

//    /**
//     * @return Article[] Returns an array of Article objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


    public function searchPaginator($data, $page)
    {
        $query = $this->createQueryBuilder('Article');
        $query->leftJoin('Article.source', 'Source');
        if (isset($data["source"]) && !empty($data["source"])) {
            $query->andWhere('UPPER(Source.name) LIKE UPPER(:source)')
                ->setParameter('source', '%' . $data["source"] . '%');
        }
        if (isset($data["name"]) && !empty($data["name"])) {
            $query->andWhere('UPPER(Article.name) LIKE UPPER(:name)')
                ->setParameter('name', '%' . $data["name"] . '%');
        }
        if (isset($data["content"]) && !empty($data["content"])) {
            $query->andWhere('UPPER(Article.content) LIKE UPPER(:content)')
                ->setParameter('content', '%' . $data["content"] . '%');
        }
        if (isset($data["pubDate"]) && !empty($data["pubDate"])) {
            $pubDateFormated = date("Y-m-d H:i:s", strtotime($data["pubDate"]));
            $query->andWhere("Demande.pubDate = :pubDate")
                ->setParameter('pubDate', $pubDateFormated);
        }
        $query->setFirstResult(($page - 1) * 10)
            ->setMaxResults(10);
        return $query->getQuery()->getResult();
    }
}
