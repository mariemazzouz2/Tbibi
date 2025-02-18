<?php
namespace App\Controller;

use App\Entity\Vote;
use App\Entity\Reponse;
use App\Entity\Question;

use App\Entity\Utilisateur;
use App\Enum\TypeVote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VoteController extends AbstractController
{
    #[Route('/vote/{id}/{type}', name: 'vote', methods: ['GET'])]
    public function vote(Reponse $reponse, string $type, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur avec l'ID 1
        $user = $entityManager->getRepository(Utilisateur::class)->find(1);
        if (!$user) {
            $this->addFlash('danger', "L'utilisateur avec l'ID 1 n'existe pas.");
            return $this->redirectToRoute('question_show', ['id' => $reponse->getQuestion()->getId()]);
        }

        // Vérifier si l'utilisateur a déjà voté sur cette réponse
        $existingVote = $entityManager->getRepository(Vote::class)->findOneBy([
            'medecin' => $user,
            'reponse' => $reponse
        ]);

        if ($existingVote) {
            // Si le vote est identique, supprimer le vote
            if ($existingVote->getValeur()->value === $type) {
                $entityManager->remove($existingVote);
                $entityManager->flush();
                return $this->redirectToRoute('question_show', ['id' => $reponse->getQuestion()->getId()]);
            }
            // Sinon, changer le vote
            $existingVote->setValeur(TypeVote::from($type));
        } else {
            // Créer un nouveau vote
            $vote = new Vote();
            $vote->setMedecin($user);
            $vote->setReponse($reponse);
            $vote->setValeur(TypeVote::from($type));
            $entityManager->persist($vote);
        }

        $entityManager->flush();
        return $this->redirectToRoute('question_show', ['id' => $reponse->getQuestion()->getId()]);
    }
}
