<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Categorie;
use App\Repository\CategorieRepository;

class CategorieService
{
    private CategorieRepository $categorieRepository;
    private EntityManagerInterface $entityManager;
    public function __construct(
        CategorieRepository $categorieRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->categorieRepository = $categorieRepository;
        $this->entityManager = $entityManager;
    }
    public function getAllCategories(): array
    {
        return $this->categorieRepository->getAllCategories();
    }
    public function getCategorieById(int $id): ?Categorie
    {
        return $this->categorieRepository->getCategorieById($id);
    }
    public function ajouterCategorie(Categorie $categorie): void
    {

        $this->entityManager->persist($categorie);
        $this->entityManager->flush();
    }
    public function modifierCategorie(Categorie $Categorie): void
    {
        $this->entityManager->flush();
    }

    // Supprimer un événement
    public function supprimerCategorie(Categorie $categorie): bool
    {
        if (!$categorie->getEvenements()->isEmpty()) {
            return false;
        }

        $this->entityManager->remove($categorie);
        $this->entityManager->flush();

        return true;
    }
}
