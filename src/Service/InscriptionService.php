<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;

class InscriptionService
{
    private InscriptionRepository $inscriptionRepository;
    private EvenementService $evenementService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        InscriptionRepository $inscriptionRepository,
        EvenementService $evenementService,
        EntityManagerInterface $entityManager
    ) {
        $this->inscriptionRepository = $inscriptionRepository;
        $this->evenementService = $evenementService;
        $this->entityManager = $entityManager;
    }

    public function peutSinscrire(Evenement $e): bool
    {
        return !$this->evenementService->estComplet($e);
    }

    public function isEmailDejaInscrit(Evenement $e, string $email): bool
    {
        return $this->inscriptionRepository
            ->isEmailDejaInscrit($e, $email);
    }

    public function enregistrerInscription(
        Inscription $i,
        Evenement $e
    ): void {

        if (!$this->peutSinscrire($e)) {
            throw new \Exception("Evenement complet");
        }

        if ($this->isEmailDejaInscrit(
            $e,
            $i->getEmailParticipant()
        )) {
            throw new \Exception("Email deja inscrit");
        }

        $i->setEvenement($e);

        $i->setDateInscription(new \DateTimeImmutable());

        $this->entityManager->persist($i);

        $this->entityManager->flush();
    }
    public function getInscriptionsParEvenement(Evenement $e): array
    {
        return $this->inscriptionRepository
            ->getInscriptionsParEvenement($e);
    }
    public function getInscriptionById(int $id): ?Inscription
    {
        return $this->inscriptionRepository->find($id);
    }
    public function annulerInscription(Inscription $i): void
    {
        $i->setStatut('ANNULE');

        $this->entityManager->flush();
    }
    public function getInscriptionsParUser(User $user): array
    {
        return $this->inscriptionRepository
            ->getInscriptionsParUser($user);
    }
}
