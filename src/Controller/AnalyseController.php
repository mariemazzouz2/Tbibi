<?php

namespace App\Controller;

use App\Entity\Analyse;
use App\Form\AnalyseType;
use App\Repository\AnalyseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Security;

#[Route('/analyse')]
final class AnalyseController extends AbstractController
{
    #[Route('/{dossierId}', name: 'app_analyse_index', methods: ['GET'], requirements: ['dossierId' => '\d+'])]
    public function index(int $dossierId, AnalyseRepository $analyseRepository): Response
    {
        // Récupérer les analyses en filtrant par dossier_id
        $analyses = $analyseRepository->findBy(['dossier' => $dossierId]);
    
        return $this->render('/analyse/index.html.twig', [
            'analyses' => $analyses,
        ]);
    }

    /*#[Route('/new', name: 'app_analyse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $analyse = new Analyse();
        $form = $this->createForm(AnalyseType::class, $analyse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($analyse);
            $entityManager->flush();

            return $this->redirectToRoute('app_analyse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('analyse/new.html.twig', [
            'analyse' => $analyse,
            'form' => $form,
        ]);
    }*/

    #[Route('/new', name: 'app_analyse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, Security $security)
    {
        $analyse = new Analyse();
        $form = $this->createForm(AnalyseType::class, $analyse);
        $form->handleRequest($request);
    
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
    
        // Initialiser la variable dossierId
        $dossierId = null;
    
        // Vérifier si l'utilisateur est connecté et s'il a un dossier médical
        if ($user && $user->getDossierMedical()) {
            $dossierMedical = $user->getDossierMedical();
            // Assigner le dossier médical à l'analyse
            $analyse->setDossier($dossierMedical);
            $dossierId = $dossierMedical->getId(); // Assuming you have a getId() method for dossier
        } else {
            // Si l'utilisateur n'a pas de dossier médical, afficher un message d'erreur et rediriger
            $this->addFlash('error', 'Aucun dossier médical trouvé pour cet utilisateur.');
            return $this->redirectToRoute('app_dossier_medical_new');
        }
    
        // Vérifier si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le fichier uploadé
            $file = $form->get('donneesAnalyse')->getData();
            
            if ($file) {
                // Gérer le nom du fichier
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
    
                try {
                    // Déplacer le fichier dans le répertoire d'uploads
                    $file->move($this->getParameter('uploads_directory'), $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                }
    
                // Enregistrer le nom du fichier dans l'entité Analyse
                $analyse->setDonneesAnalyse($newFilename);
            }
    
            // Persist et flush l'analyse dans la base de données
            $entityManager->persist($analyse);
            $entityManager->flush();
    
            // Rediriger après la création de l'analyse avec le dossierId
            return $this->redirectToRoute('app_analyse_index', ['dossierId' => $dossierId], Response::HTTP_SEE_OTHER);
        }
            // Vérification des erreurs
        $errors = $validator->validate($analyse);
        // Rendre le formulaire
        return $this->render('analyse/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
        ]);
    }
    

    #[Route('/show/{id}', name: 'app_analyse_show', methods: ['GET'])]
    public function show(Analyse $analyse): Response
    {
        return $this->render('analyse/show.html.twig', [
            'analyse' => $analyse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_analyse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Analyse $analyse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnalyseType::class, $analyse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_analyse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('analyse/edit.html.twig', [
            'analyse' => $analyse,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_analyse_delete', methods: ['POST'])]
    public function delete(Request $request, Analyse $analyse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$analyse->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($analyse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_analyse_index', [], Response::HTTP_SEE_OTHER);
    }
}
