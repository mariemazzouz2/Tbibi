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

#[Route('/backdoctor/dossier/medical')]
final class backdoctorDossierMedicalController extends AbstractController
{
    #[Route(name: 'app_backdoctor_dossier_medical_index', methods: ['GET'])]
    public function backdoctorindex(DossierMedicalRepository $dossierMedicalRepository): Response
    {
        return $this->render('backdoctor/dossier_medical/index.html.twig', [
            'dossier_medicals' => $dossierMedicalRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_backdoctor_dossier_medical_new', methods: ['GET', 'POST'])]
    public function backdoctornew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dossierMedical = new DossierMedical();
        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dossierMedical);
            $entityManager->flush();

            return $this->redirectToRoute('app_backdoctor_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backdoctor/dossier_medical/new.html.twig', [
            'dossier_medical' => $dossierMedical,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id}', name: 'app_backdoctor_dossier_medical_show', methods: ['GET'])]
    public function backdoctorshow(DossierMedical $dossierMedical): Response
    {
        return $this->render('backdoctor/dossier_medical/show.html.twig', [
            'dossier_medical' => $dossierMedical,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backdoctor_dossier_medical_edit', methods: ['GET', 'POST'])]
    public function backdoctoredit(Request $request, DossierMedical $dossierMedical, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_backdoctor_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backdoctor/dossier_medical/edit.html.twig', [
            'dossier_medical' => $dossierMedical,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backdoctor_dossier_medical_delete', methods: ['POST'])]
    public function backdoctordelete(Request $request, DossierMedical $dossierMedical, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dossierMedical->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($dossierMedical);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_backdoctor_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
    }
}
