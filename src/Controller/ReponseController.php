<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Reponse; 
use App\Repository\ReponseRepository; 
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\Question; // Assure-toi que cette ligne est bien présente
use Doctrine\ORM\EntityManagerInterface;
final class ReponseController extends AbstractController
{
    #[Route('/reponse', name: 'app_reponse')]
    public function index(): Response
    {
        return $this->render('reponse/index.html.twig', [
            'controller_name' => 'ReponseController',
        ]);
    }

    #[Route('/delete/{id}', name: 'app_reponse_delete')]
    public function delete(Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($reponse);
        $entityManager->flush();

        $this->addFlash('success', 'Réponse supprimée avec succès.');

        return $this->redirectToRoute('app_forum_doc'); // Rediriger vers la liste des questions
    }

#[Route('/reponse/resp/delete/{id}', name: 'admin_reponse_deletee')]
    public function deletee(ReponseRepository $reponseRepository, EntityManagerInterface $entityManager, $id): RedirectResponse
    {
        // Récupérer la réponse
        $reponse = $reponseRepository->find($id);
        

        if (!$reponse) {
            // Si la réponse n'existe pas, redirige avec un message d'erreur
            $this->addFlash('error', 'Réponse non trouvée.');
            return $this->redirectToRoute('app_forum_admin');
        }
        // Supprimer la réponse si elle existe
        $entityManager->remove($reponse);
        $entityManager->flush();

        // Redirection après suppression
        return $this->redirectToRoute('app_forum_admin');
    }
    #[Route('/reponses/{id}', name: 'view_reponses')]
    public function viewReponses(Question $question, ReponseRepository $reponseRepository): Response
    {
        $reponses = $reponseRepository->findBy(['question' => $question]);

        return $this->render('reponse/listadmin.html.twig', [
            'question' => $question,
            'reponses' => $reponses,
        ]);
    }


 
}
