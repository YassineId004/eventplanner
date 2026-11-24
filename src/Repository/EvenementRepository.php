<?php

namespace App\Repository;

use App\Entity\Evenement;
use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    public function getEvenementsAVenir(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.dateDebut >= :date')
            ->setParameter('date', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function getEvenementsParCategorie(Categorie $cat): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.categorie = :categorie')
            ->andWhere('e.dateDebut >= :date')
            ->setParameter('categorie', $cat)
            ->setParameter('date', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function getNombreInscriptions(Evenement $e): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(i.id)
                 FROM App\Entity\Inscription i
                 WHERE i.evenement = :event
                 AND i.statut = :statut'
            )
            ->setParameter('event', $e)
            ->setParameter('statut', 'CONFIRME')
            ->getSingleScalarResult();
    }
    public function getEvenementById(int $id): ?Evenement
    {
        return $this->find($id);
    }
}
