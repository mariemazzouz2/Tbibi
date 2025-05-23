<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Form\DossierMedicalType;
use App\Repository\DossierMedicalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


#[Route('/dossier/medical')]
final class DossierMedicalController extends AbstractController
{
    #[Route(name: 'app_dossier_medical_index', methods: ['GET'])]
    public function index(DossierMedicalRepository $dossierMedicalRepository): Response
    {
        return $this->render('dossier_medical/index.html.twig', [
            'dossier_medicals' => $dossierMedicalRepository->findAll(),
        ]);
    }

    #[Route('/download/{filename}', name: 'app_dossier_medical_download')]
    public function download(string $filename): BinaryFileResponse
    {
        $filePath = $this->getParameter('uploads_directory') . '/' . $filename;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException("Le fichier demandé n'existe pas.");
        }

        return new BinaryFileResponse($filePath);
    }

    #[Route('/new', name: 'app_dossier_medical_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $dossierMedical = new DossierMedical();
    $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer le fichier téléchargé
        $fichier = $form->get('fichier')->getData();

        // Vérifier si un fichier a été téléchargé
        if ($fichier) {
            // Générer un nom unique pour le fichier
            $filename = uniqid() . '.' . $fichier->guessExtension();

            // Déplacer le fichier dans un répertoire spécifique (vous pouvez configurer ce répertoire dans les paramètres)
            $fichier->move($this->getParameter('uploads_directory'), $filename);

            // Assigner le nom du fichier à l'entité DossierMedical
            $dossierMedical->setFichier($filename);
        }

        // Persister l'entité DossierMedical
        $entityManager->persist($dossierMedical);
        $entityManager->flush();

        // Rediriger vers la page index après l'ajout du dossier médical
        return $this->redirectToRoute('app_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
    }
    // Vérification des erreurs
    //$errors = $validator->validate($analyse);
    // Rendu du formulaire
    return $this->render('dossier_medical/new.html.twig', [
        'dossier_medical' => $dossierMedical,
        'form' => $form,
//        'errors' => $errors,

    ]);
}


    #[Route('/show/{id}', name: 'app_dossier_medical_show', methods: ['GET'])]
    public function show(DossierMedical $dossierMedical): Response
    {
        
        return $this->render('dossier_medical/show.html.twig', [
            'dossier_medical' => $dossierMedical,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_dossier_medical_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DossierMedical $dossierMedical, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dossier_medical/edit.html.twig', [
            'dossier_medical' => $dossierMedical,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_dossier_medical_delete', methods: ['POST'])]
    public function delete(Request $request, DossierMedical $dossierMedical, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dossierMedical->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($dossierMedical);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
    }
}