<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Service\CategorieService;
use App\Form\CategorieType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CategorieController extends AbstractController
{
    // Afficher toutes les catégories
    #[Route('/categorie', name: 'app_categorie')]
    public function index(CategorieService $categorieService): Response
    {
        $categories = $categorieService->getAllCategories();

        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    // Ajouter une catégorie
    #[Route('/categorie/ajouter', name: 'app_categorie_ajouter')]
    public function ajouter(
        Request $request,
        CategorieService $categorieService
    ): Response {
        $categorie = new Categorie();

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $categorieService->ajouterCategorie($categorie);

            $this->addFlash('success', 'Catégorie ajoutée avec succès.');

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Modifier une catégorie
    #[Route('/categorie/modifier/{id}', name: 'app_categorie_modifier')]
    public function modifier(
        int $id,
        Request $request,
        CategorieService $categorieService
    ): Response {
        $categorie = $categorieService->getCategorieById($id);

        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie introuvable.');
        }

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $categorieService->modifierCategorie($categorie);

            $this->addFlash('success', 'Catégorie modifiée avec succès.');

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/modifier.html.twig', [
            'form' => $form->createView(),
            'categorie' => $categorie,
        ]);
    }


    #[Route('/categorie/supprimer/{id}', name: 'app_categorie_supprimer', methods: ['POST'])]
    public function supprimer(
        int $id,
        Request $request,
        CategorieService $categorieService
    ): Response {
        $categorie = $categorieService->getCategorieById($id);

        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie introuvable.');
        }

        if ($this->isCsrfTokenValid(
            'supprimer' . $categorie->getId(),
            $request->request->get('_token')
        )) {
            $resultat = $categorieService->supprimerCategorie($categorie);

            if ($resultat) {
                $this->addFlash('success', 'Catégorie supprimée avec succès.');
            } else {
                $this->addFlash('danger', 'Impossible de supprimer cette catégorie car elle contient des événements.');
            }
        } else {
            $this->addFlash('danger', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_categorie');
    }
}
