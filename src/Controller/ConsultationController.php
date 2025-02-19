<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Form\ConsultationType;
use App\Repository\ConsultationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ConsultationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/patient/consultations', name: 'app_patient_consultations')]
    public function patientConsultations(ConsultationRepository $consultationRepository): Response
    {
        // Hardcoded patient_id = 1 as requested
        //$patient_id = 1;
        $patient_id = $this->getUser()->getId();
        $consultations = $consultationRepository->findBy(
            ['patient' => $patient_id],
            ['dateC' => 'DESC']
        );

        return $this->render('patient/consultations.html.twig', [
            'consultations' => $consultations,
        ]);
    }

    #[Route('/consultation/{id}/view', name: 'app_consultation_view')]
    public function viewConsultation(Consultation $consultation): Response
    {
        // Hardcoded patient_id = 1 as requested
        if ($consultation->getPatient()->getId() != $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can only view your own consultations.');
        }

        return $this->render('patient/consultation_details.html.twig', [
            'consultation' => $consultation,
        ]);
    }

    #[Route('/consultation/{id}/edit', name: 'app_consultation_edit')]
    public function editConsultation(Request $request, Consultation $consultation): Response
    {
        // Check if consultation belongs to current patient
        if ($consultation->getPatient()->getId() != $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can only edit your own consultations.');
        }

        // Check if consultation is pending
        if ($consultation->getStatus() !== 'pending') {
            $this->addFlash('error', 'You can only modify pending consultations.');
            return $this->redirectToRoute('app_patient_consultations');
        }

        $form = $this->createForm(ConsultationType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Consultation updated successfully.');
            return $this->redirectToRoute('app_patient_consultations');
        }

        return $this->render('patient/consultation_edit.html.twig', [
            'consultation' => $consultation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/consultation/{id}/delete', name: 'app_consultation_delete', methods: ['POST'])]
    public function deleteConsultation(Request $request, Consultation $consultation): Response
    {
        // Check if consultation belongs to current patient
        if ($consultation->getPatient()->getId() !=$this->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can only delete your own consultations.');
        }

        // Check if consultation is pending
        if ($consultation->getStatus() !== 'pending') {
            $this->addFlash('error', 'You can only delete pending consultations.');
            return $this->redirectToRoute('app_patient_consultations');
        }

        // Verify CSRF token
        if ($this->isCsrfTokenValid('delete'.$consultation->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($consultation);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Consultation cancelled successfully.');
        }

        return $this->redirectToRoute('app_patient_consultations');
    }
} 