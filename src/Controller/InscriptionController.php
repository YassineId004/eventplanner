<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Inscription;
use App\Service\InscriptionService;
use App\Service\EvenementService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function index(): Response
    {
        return $this->render('inscription/index.html.twig', [
            'controller_name' => 'InscriptionController',
        ]);
    }

    #[Route('/evenement/{id}/inscription', name: 'app_inscription_new', methods: ['GET', 'POST'])]
    public function inscrire(
        int $id,
        Request $request,
        EvenementService $evenementService,
        InscriptionService $inscriptionService
    ): Response {

        // Récupérer l'événement
        $evenement = $evenementService->getEvenementById($id);

        if (!$evenement) {
            throw $this->createNotFoundException('Événement introuvable');
        }

        if ($request->isMethod('POST')) {

            // Récupérer les données
            // Récupérer l'utilisateur connecté
            $user = $this->getUser();

            // Récupérer ses informations
            $user = $this->getUser();

            if (!$user instanceof User) {
                throw $this->createAccessDeniedException();
            }

            $nom = $user->getNom() . ' ' . $user->getPrenom();
            $email = $user->getEmail();
            // Vérification des champs
            if (empty($nom) || empty($email)) {

                $this->addFlash('danger', 'Tous les champs sont obligatoires.');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $this->addFlash('danger', 'Email invalide.');
            } else {

                // Créer une inscription
                // Créer une inscription
                $inscription = new Inscription();

                $inscription->setNomParticipant($nom);
                $inscription->setEmailParticipant($email);

                // Récupérer l'utilisateur connecté
                $user = $this->getUser();

                // Associer l'inscription à l'utilisateur
                $inscription->setUser($user);

                // Enregistrer l'inscription
                try {

                    $inscriptionService->enregistrerInscription(
                        $inscription,
                        $evenement
                    );

                    $this->addFlash('success', 'Inscription réussie !');

                    return $this->redirectToRoute('app_evenement');
                } catch (\Exception $e) {

                    $this->addFlash('danger', $e->getMessage());
                }
            }
        }

        return $this->render('inscription/new.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/evenement/{id}/inscriptions', name: 'app_inscription_liste', methods: ['GET'])]
    public function listeInscriptions(
        int $id,
        EvenementService $evenementService,
        InscriptionService $inscriptionService
    ): Response {

        // Récupérer l'événement
        $evenement = $evenementService->getEvenementById($id);

        if (!$evenement) {
            throw $this->createNotFoundException('Événement introuvable');
        }

        // Récupérer les inscriptions de cet événement
        $inscriptions = $inscriptionService->getInscriptionsParEvenement($evenement);

        return $this->render('inscription/liste.html.twig', [
            'evenement' => $evenement,
            'inscriptions' => $inscriptions,
        ]);
    }

    #[Route('/inscription/{id}/annuler', name: 'app_inscription_annuler', methods: ['POST'])]
    public function annuler(
        int $id,
        Request $request,
        InscriptionService $inscriptionService
    ): Response {

        // Récupérer l'inscription
        $inscription = $inscriptionService->getInscriptionById($id);

        if (!$inscription) {
            throw $this->createNotFoundException('Inscription introuvable');
        }

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Vérifier que l'inscription appartient à l'utilisateur
        if ($inscription->getUser() !== $user) {
            throw $this->createAccessDeniedException(
                'Vous ne pouvez pas annuler cette inscription.'
            );
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid(
            'annuler' . $id,
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Vérifier le statut
        if ($inscription->getStatut() === 'ANNULE') {
            $this->addFlash('danger', 'Cette inscription est déjà annulée.');
        } else {
            $inscriptionService->annulerInscription($inscription);
            $this->addFlash('success', 'Inscription annulée avec succès !');
        }

        return $this->redirectToRoute('app_mes_inscriptions');
    }
    #[Route('/mes-inscriptions', name: 'app_mes_inscriptions', methods: ['GET'])]
    public function mesInscriptions(
        InscriptionService $inscriptionService
    ): Response {

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Récupérer ses inscriptions
        $inscriptions = $inscriptionService->getInscriptionsParUser($user);

        return $this->render('inscription/mes_inscriptions.html.twig', [
            'inscriptions' => $inscriptions,
        ]);
    }
}
