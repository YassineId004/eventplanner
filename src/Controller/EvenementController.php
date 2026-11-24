<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Service\EvenementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CategorieService;
use App\Entity\Evenement;
use App\Form\EvenementType;

final class EvenementController extends AbstractController
{
    #[Route('/evenement', name: 'app_evenement')]
    public function index(
        Request $request,
        EvenementService $evenementService,
        CategorieService $categorieService
    ): Response {

        // Récupérer l'id de la catégorie depuis l'URL
        $categorieId = $request->query->get('categorie');

        $categorieSelectionnee = null;

        if ($categorieId !== null && $categorieId !== '') {

            $categorieSelectionnee = $categorieService->getCategorieById((int) $categorieId);

            if (!$categorieSelectionnee) {
                throw $this->createNotFoundException('Catégorie introuvable');
            }

            $evenements = $evenementService->getEvenementsParCategorie($categorieSelectionnee);
        } else {

            $evenements = $evenementService->getEvenementsAVenir();
        }

        $statuts = [];
        $nombresInscriptions = [];

        foreach ($evenements as $evenement) {

            $id = $evenement->getId();

            // Récupérer le nombre d'inscriptions confirmées
            $nombresInscriptions[$id] = $evenementService->getNombreInscriptions($evenement);

            // Vérifier si l'événement est complet
            $statuts[$id] = $nombresInscriptions[$id] >= $evenement->getCapaciteMax();
        }
        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
            'statuts' => $statuts,
            'nombresInscriptions' => $nombresInscriptions,
            'categories' => $categorieService->getAllCategories(),
            'categorieSelectionnee' => $categorieSelectionnee,
        ]);
    }


    #[Route(
        '/evenement/{id}',
        name: 'app_evenement_show',
        requirements: ['id' => '\d+']
    )]    public function show(
        int $id,
        EvenementService $evenementService
    ): Response {

        $evenement = $evenementService->getEvenementById($id);

        if (!$evenement) {
            throw $this->createNotFoundException('Événement introuvable');
        }

        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }
    #[Route('/evenement/ajouter', name: 'app_evenement_add')]
    public function ajouter(
        Request $request,
        EvenementService $evenementService
    ): Response {

        // Création d'un nouvel événement
        $evenement = new Evenement();

        // Création du formulaire
        $form = $this->createForm(EvenementType::class, $evenement);

        // Récupération des données
        $form->handleRequest($request);

        // Vérification du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            $evenementService->ajouterEvenement($evenement);

            $this->addFlash('success', 'Événement ajouté avec succès');

            return $this->redirectToRoute('app_evenement');
        }

        return $this->render('evenement/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/evenement/{id}/modifier', name: 'app_evenement_edit', requirements: ['id' => '\d+'])]
    public function modifier(
        int $id,
        Request $request,
        EvenementService $evenementService
    ): Response {

        // Récupérer l'événement
        $evenement = $evenementService->getEvenementById($id);

        if (!$evenement) {
            throw $this->createNotFoundException('Événement introuvable');
        }

        // Créer le formulaire avec les anciennes données
        $form = $this->createForm(EvenementType::class, $evenement);

        // Récupérer les nouvelles données
        $form->handleRequest($request);

        // Vérifier le formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            $evenementService->modifierEvenement($evenement);

            $this->addFlash('success', 'Événement modifié avec succès');

            return $this->redirectToRoute('app_evenement');
        }

        return $this->render('evenement/modifier.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement,
        ]);
    }
    #[Route('/evenement/{id}/supprimer', name: 'app_evenement_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function supprimer(
        int $id,
        Request $request,
        EvenementService $evenementService
    ): Response {

        // Récupérer l'événement
        $evenement = $evenementService->getEvenementById($id);

        if (!$evenement) {
            throw $this->createNotFoundException('Événement introuvable');
        }

        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {

            $evenementService->supprimerEvenement($evenement);

            $this->addFlash('success', 'Événement supprimé avec succès');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('app_evenement');
    }
}
