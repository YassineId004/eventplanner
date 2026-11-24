<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Evenement;
use App\Entity\Inscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    public function isEmailDejaInscrit(
        Evenement $e,
        string $email
    ): bool {
        $inscription = $this->createQueryBuilder('i')
            ->where('i.evenement = :event')
            ->andWhere('i.emailParticipant = :email')
            ->andWhere('i.statut = :statut')
            ->setParameter('event', $e)
            ->setParameter('email', $email)
            ->setParameter('statut', 'CONFIRME')
            ->getQuery()
            ->getOneOrNullResult();

        return $inscription !== null;
    }
    public function getInscriptionsParEvenement(Evenement $e): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.evenement = :event')
            ->andWhere('i.statut = :statut')
            ->setParameter('event', $e)
            ->setParameter('statut', 'CONFIRME')
            ->getQuery()
            ->getResult();
    }
    public function getInscriptionsParUser(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
