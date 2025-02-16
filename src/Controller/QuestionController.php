<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Question;
use App\Form\QuestionType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Utilisateur;
use App\Enum\Specialite;
use App\Entity\Reponse;
use App\Form\ReponseType;
use Symfony\Component\HttpFoundation\JsonResponse;
final class QuestionController extends AbstractController
{
    #[Route('/questions', name: 'app_forum')]
    public function list(QuestionRepository $questionRepository): Response
    {    $userId = 1; // ID de l'utilisateur spécifique
        //$this->getUser()
        $questions = $questionRepository->findBy(['patient' => $userId]);


        return $this->render('question/list.html.twig', [
            'questions' => $questions,
        ]);
    }

    #[Route('/admin/questions', name: 'app_forum_admin')]
    public function listadmin(QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findAll();


        return $this->render('question/listadmin.html.twig', [
            'questions' => $questions,
        ]);
    }

    #[Route('/questions/search', name: 'question_search')]
    public function search(Request $request, QuestionRepository $questionRepository): JsonResponse
    {
        try {
            $query = $request->query->get('query');  // Récupérer le paramètre query
            if (!$query) {
                return new JsonResponse(['error' => 'Query parameter is required'], 400);  // Si la query est vide
            }
    
            // Effectuer la recherche
            $questions = $questionRepository->findBySearchQuery($query);
            
            // Format des données pour la réponse
            $responseData = [];
            foreach ($questions as $question) {
                $responseData[] = [
                    'id' => $question->getId(),
                    'titre' => $question->getTitre(),
                    'contenu' => $question->getContenu(),
                    'patient' => ['nom' => $question->getPatient()->getNom()],
                ];
            }
    
            return new JsonResponse($responseData);
        } catch (\Exception $e) {
            // Si une erreur se produit, retourne l'exception
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    

    
    #[Route('/docteur/questions', name: 'app_forum_doc')]
    public function listdoct(QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findAll();

        return $this->render('question/listdoct.html.twig', [
            'questions' => $questions,
        ]);
    }

    #[Route('/questions/new', name: 'app_question_add', methods: ['GET', 'POST'])]
    public function newQuestion(Request $request, EntityManagerInterface $entityManager): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setDateCreation(new \DateTimeImmutable());

            // Gestion de l'upload de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $question->setImage($newFilename);
            }
            
            //$user = $this->getUser();
            //if ($user) {
             //   $question->setPatient($user);
            //}
            $user = $entityManager->getRepository(Utilisateur::class)->find(1);
            if ($user) {
                $question->setPatient($user);
            }
            $entityManager->persist($question);
            $entityManager->flush();

            return $this->redirectToRoute('app_forum');
        }

        return $this->render('question/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_question_edit')]
    public function edit(Question $question, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer le formulaire pour modifier la question
        $form = $this->createForm(QuestionType::class, $question);

        // Traitement du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder les changements en base de données
            $entityManager->flush();

            // Message flash de succès
            $this->addFlash('success', 'La question a été mise à jour avec succès.');

            // Rediriger vers la liste des questions
            return $this->redirectToRoute('app_forum_admin');
        }

        return $this->render('question/edit.html.twig', [
            'form' => $form->createView(),
            'question' => $question,
        ]);
    }
    #[Route('/question/{id}', name: 'question_show', methods: ['GET', 'POST'])]
    public function showQuestion(Question $question, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setDateReponse(new \DateTimeImmutable());
            $reponse->setQuestion($question);

            // Associer l'utilisateur avec l'ID 1 comme médecin
            $user = $entityManager->getRepository(Utilisateur::class)->find(1);
            if ($user) {
                $reponse->setMedecin($user);
            }

            $entityManager->persist($reponse);
            $entityManager->flush();

            return $this->redirectToRoute('question_show', ['id' => $question->getId()]);
        }

        return $this->render('reponse/reponse.html.twig', [
            'question' => $question,
            'reponses' => $question->getReponses(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/question/delete/{id}', name: 'app_question_delete')]
public function delete(Question $question, EntityManagerInterface $entityManager): Response
{
    $entityManager->remove($question);
    $entityManager->flush();

    return $this->redirectToRoute('app_forum');
}
#[Route('/question/deletedelete//{id}', name: 'app_question_delete_admin')]
public function deletee(Question $question, EntityManagerInterface $entityManager): Response
{
    $entityManager->remove($question);
    $entityManager->flush();

    return $this->redirectToRoute('app_forum_admin');
}
    }
