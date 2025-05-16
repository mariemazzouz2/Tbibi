<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Question;
use App\Entity\Notii;
use App\Repository\NotiiRepository;
use App\Service\MailService;
use App\Repository\ReponseRepository;
use App\Form\QuestionType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Utilisateur;
use App\Entity\Reponse;
use App\Form\ReponseType;
use Symfony\Component\HttpFoundation\JsonResponse;


final class QuestionController extends AbstractController
{
    #[Route('/questions', name: 'app_forum')]
    public function list(
        QuestionRepository $questionRepository, 
        ReponseRepository $reponseRepository, 
        NotiiRepository $notiiRepository, 
        MailService $mailService
    ): Response {
        $user = $this->getUser();
        $questions = $questionRepository->findBy(['patient' => $user->getId()]);
        $notifications = $notiiRepository->findBy(['touser' => $user]);
    
        // Créer un tableau pour stocker les réponses triées
        $sortedReponses = [];
        foreach ($questions as $question) {
            $sortedReponses[$question->getId()] = $reponseRepository->findByMostVoted($question);
        }
    
        return $this->render('question/list.html.twig', [
            'questions' => $questions,
            'notifications' => $notifications,
            'sortedReponses' => $sortedReponses // Passer les réponses triées au template
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
            $query = $request->query->get('query');
            if (!$query) {
                return new JsonResponse(['error' => 'Query parameter is required'], 400);
            }

            $questions = $questionRepository->findBySearchQuery($query);
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

            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $question->setImage($newFilename);
            }

            $user = $this->getUser();  // Utiliser l'utilisateur connecté
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
    // Vérifier que la question existe
    if (!$question) {
        throw $this->createNotFoundException('Question non trouvée');
    }
    
    $form = $this->createForm(QuestionType::class, $question);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        // Forcer la mise à jour même si le formulaire n'est pas valide
        $entityManager->flush();
        
        // Ajouter un message flash pour informer l'utilisateur
        $this->addFlash('success', 'La question a été mise à jour avec succès.');

        // Rediriger vers la page d'administration
        return $this->redirectToRoute('app_forum_admin');
    } else {
        // Si des erreurs sont présentes dans le formulaire
        $this->addFlash('error', 'Des erreurs ont été détectées dans le formulaire.');
    }

    return $this->render('question/edit.html.twig', [
        'form' => $form->createView(),
        'question' => $question,
    ]);
}

#[Route('/question/{id}', name: 'question_show', methods: ['GET', 'POST'])]
public function showQuestion(
    Question $question, 
    Request $request, 
    EntityManagerInterface $entityManager, 
    MailService $mailService // Injection du service MailService
): Response 
{
    $reponse = new Reponse();
    $form = $this->createForm(ReponseType::class, $reponse);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $reponse->setDateReponse(new \DateTimeImmutable());
        $reponse->setQuestion($question);

        $user = $this->getUser(); // Médecin connecté
        if ($user) {
            $reponse->setMedecin($user);
        }

        $entityManager->persist($reponse);
        $entityManager->flush();

        // 📧 Envoi d'email au patient
        $patient = $question->getPatient();
        if ($patient) {
            try {
                $mailService->sendNotificationEmail(
                    $patient->getEmail(),
                    "Nouvelle réponse à votre question",
                    'emails/notification.html.twig',
                    [
                        'patient' => $patient,
                        'question' => $question,
                        'reponse' => $reponse
                    ]
                );

                $this->addFlash('success', 'Votre réponse a été ajoutée et un email de notification a été envoyé au patient.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'La réponse a été ajoutée, mais l\'email n\'a pas pu être envoyé.');
            }
        }

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

    #[Route('/question/deletedelete/{id}', name: 'app_question_delete_admin')]
    public function deletee(Question $question, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($question);
        $entityManager->flush();
        return $this->redirectToRoute('app_forum_admin');
    }

}
