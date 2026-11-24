<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Evenement;
use App\Entity\Categorie;
use App\Repository\EvenementRepository;

class EvenementService
{
    private EvenementRepository $evenementRepository;
    private EntityManagerInterface $entityManager;
    public function __construct(
        EvenementRepository $evenementRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->evenementRepository = $evenementRepository;
        $this->entityManager = $entityManager;
    }

    public function getEvenementsAVenir(): array
    {
        return $this->evenementRepository
            ->getEvenementsAVenir();
    }

    public function getEvenementsParCategorie(Categorie $cat): array
    {
        return $this->evenementRepository
            ->getEvenementsParCategorie($cat);
    }

    public function getNombreInscriptions(Evenement $e): int
    {
        return $this->evenementRepository
            ->getNombreInscriptions($e);
    }

    public function estComplet(Evenement $e): bool
    {
        return $this->getNombreInscriptions($e)
            >= $e->getCapaciteMax();
    }
    public function getEvenementById(int $id): ?Evenement
    {
        return $this->evenementRepository->getEvenementById($id);
    }

    public function ajouterEvenement(Evenement $evenement): void
    {
        $evenement->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($evenement);
        $this->entityManager->flush();
    }
    // Modifier un événement
    public function modifierEvenement(Evenement $evenement): void
    {
        $this->entityManager->flush();
    }

    // Supprimer un événement
    public function supprimerEvenement(Evenement $evenement): void
    {
        $this->entityManager->remove($evenement);
        $this->entityManager->flush();
    }
}
