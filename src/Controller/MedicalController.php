<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\Ordonnance;
use App\Form\ConsultationType;
use App\Form\OrdonnanceType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\ConsultationMeetService;
//use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\UtilisateurRepository;
use App\Enum\TypeConsultation;
use App\Repository\ConsultationRepository;
use App\Repository\OrdonnanceRepository;

class MedicalController extends AbstractController
{
    private ConsultationMeetService $consultationMeetService;

    public function __construct(
        private EntityManagerInterface $entityManager,
      //  private EmailService $emailService,
        private UtilisateurRepository $utilisateurRepository,
        ConsultationMeetService $consultationMeetService
    ) {
        $this->consultationMeetService = $consultationMeetService;
    }
    

    #[Route('/appointment/request', name: 'app_appointment_request')]
    // TODO: Restore after user auth integration
     #[IsGranted('ROLE_PATIENT')]
    public function requestAppointment(Request $request): Response
    {
        $consultation = new Consultation();
        
        // TODO: Replace with authenticated user after integration
         $consultation->setPatient($this->getUser());
        $patient = $this->getUser();
        if (!$patient) {
            throw $this->createNotFoundException('Patient with ID 1 not found');
        }
        $consultation->setPatient($patient);
        
        $consultation->setStatus(Consultation::STATUS_PENDING);

        $form = $this->createForm(ConsultationType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($consultation);
            $this->entityManager->flush();

            $this->addFlash('success', 'Appointment request submitted successfully');
            return $this->redirectToRoute('app_patient_consultations');
        }

        return $this->render('consultation/request.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/consultation/{id}/manage', name: 'app_consultation_manage', methods: ['POST'])]
    public function manageConsultation(Request $request, Consultation $consultation): Response
    {
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('manage_consultation_' . $consultation->getId(), $submittedToken)) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('app_medical_dashboard');
        }

        $consultation->setDateC(new \DateTime($request->request->get('dateC')));
        
        // Convert type string to TypeConsultation enum
        $type = match($request->request->get('type')) {
            'online' => TypeConsultation::ONLINE,
            'in_person' => TypeConsultation::ON_SPOT,
            default => throw new \InvalidArgumentException('Invalid consultation type')
        };
        $consultation->setType($type);
        
        $consultation->setCommentaire($request->request->get('commentaire'));
        $newStatus = $request->request->get('status');
        $consultation->setStatus($newStatus);

        // Generate meet link for online consultations when confirmed
        if ($type === TypeConsultation::ONLINE && $newStatus === 'confirmed') {
            if (!$consultation->getMeetLink()) {
                try {
                    // Schedule a Google Meet using our service
                    $meeting = $this->consultationMeetService->scheduleConsultationMeet($consultation);
                    
                    if ($meeting) {
                        $this->addFlash('success', 'Google Meet link generated successfully.');
                    } else {
                        $this->addFlash('warning', 'Consultation updated but failed to generate Google Meet link.');
                    }
                    
                    // TODO: Send email to patient with meet link
                    // $this->emailService->sendConsultationConfirmation($consultation);
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Consultation updated but meeting link generation failed: ' . $e->getMessage());
                }
            }
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Consultation updated successfully');
        return $this->redirectToRoute('app_medical_dashboard');
    }

    #[Route('/consultation/{id}/prescribe', name: 'app_consultation_prescribe')]
    // TODO: Restore after user auth integration
     #[IsGranted('ROLE_MEDECIN')]
    public function createPrescription(Request $request, Consultation $consultation,SluggerInterface $slugger): Response
    {
        // TODO: Add doctor authorization check after integration
         if ($consultation->getMedecin() !== $this->getUser()) { throw AccessDeniedException; }
        
        $ordonnance = new Ordonnance();
        $ordonnance->setConsultation($consultation);
        
        $form = $this->createForm(OrdonnanceType::class, $ordonnance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           // dd($form->get('signature')->getData());
            $signatureFile = $form->get('signature')->getData();
          //  dd($signatureFile);
            if ($signatureFile) {
                $originalFilename = pathinfo($signatureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$signatureFile->guessExtension();
    
                try {
                    $signatureFile->move(
                        $this->getParameter('signatures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
    
                // Update the 'signature' property to store the file name instead of its contents
                $ordonnance->setSignature($this->getParameter('signatures_directory').'/'.$newFilename);
            }
                 $this->entityManager->persist($ordonnance);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_ordonnance_show', ['id' => $ordonnance->getId()]);
        }

        return $this->render('ordonnance/create.html.twig', [
            'consultation' => $consultation,
            'form' => $form->createView()
        ]);
    }

    #[Route('/ordonnance/{id}/download', name: 'app_ordonnance_download')]
    public function downloadPrescription(Ordonnance $ordonnance): Response
    {
        // Configure Dom
        //according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Render the HTML as PDF
        $html = $this->renderView('ordonnance/pdf.html.twig', [
            'ordonnance' => $ordonnance
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output the generated PDF to Browser
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 
            'attachment;filename="ordonnance-' . $ordonnance->getId() . '.pdf"'
        );

        return $response;
    }

    #[Route('/consultation/{id}/confirm', name: 'app_consultation_confirm')]
    #[IsGranted('ROLE_MEDECIN')]
    public function confirmConsultation(Consultation $consultation): Response
    {
        $consultation->setStatus(Consultation::STATUS_CONFIRMED);

        if ($consultation->getType() === TypeConsultation::ONLINE) {
            // Use the new ConsultationMeetService to schedule a meeting
            $meeting = $this->consultationMeetService->scheduleConsultationMeet($consultation);
            
            if (!$meeting) {
                // If Google Meet scheduling fails, create a placeholder link
                $consultation->setMeetLink('https://meet.google.com/' . uniqid());
            }
            
            // TODO: Send email to patient with meet link
            // $this->emailService->sendConsultationConfirmation($consultation);
        }

        $this->entityManager->flush();

        return $this->render('consultation/confirmation_email.html.twig', [
            'consultation' => $consultation
        ]);
    }

    #[Route(path: '/doctor/consultations', name: 'app_medical_dashboard')]
    #[IsGranted('ROLE_MEDECIN')]
    public function dashboard(Request $request, ConsultationRepository $consultationRepository, OrdonnanceRepository $ordonnanceRepository): Response
    {
        $filter = $request->query->get('filter', 'all');
        
        // TODO: Replace with authenticated user after integration
         $doctor = $this->getUser();
        //$doctor = $this->utilisateurRepository->find(2);
        if (!$doctor) {
            throw $this->createNotFoundException('Doctor  not found');
        }

        $queryBuilder = $consultationRepository->createQueryBuilder('c')
            ->where('c.medecin = :doctor')
            ->setParameter('doctor', $doctor);

        // Apply filters
        switch ($filter) {
            case 'pending':
                $queryBuilder->andWhere('c.status = :status')
                    ->setParameter('status', 'pending');
                break;
            case 'today':
                $today = new \DateTime('today');
                $tomorrow = new \DateTime('tomorrow');
                $queryBuilder->andWhere('c.dateC >= :today AND c.dateC < :tomorrow')
                    ->setParameter('today', $today)
                    ->setParameter('tomorrow', $tomorrow);
                break;
            case 'week':
                $startOfWeek = new \DateTime('monday this week');
                $endOfWeek = new \DateTime('sunday this week 23:59:59');
                $queryBuilder->andWhere('c.dateC BETWEEN :start AND :end')
                    ->setParameter('start', $startOfWeek)
                    ->setParameter('end', $endOfWeek);
                break;
            case 'month':
                $startOfMonth = new \DateTime('first day of this month midnight');
                $endOfMonth = new \DateTime('last day of this month 23:59:59');
                $queryBuilder->andWhere('c.dateC BETWEEN :start AND :end')
                    ->setParameter('start', $startOfMonth)
                    ->setParameter('end', $endOfMonth);
                break;
            case 'upcoming':
                $now = new \DateTime();
                $queryBuilder->andWhere('c.dateC > :now')
                    ->setParameter('now', $now)
                    ->andWhere('c.status = :status')
                    ->setParameter('status', 'confirmed');
                break;
        }

        $consultations = $queryBuilder->orderBy('c.dateC', 'ASC')->getQuery()->getResult();
        $prescriptions = $ordonnanceRepository->findBy(['consultation' => $consultations]);

        return $this->render('medical/dashboard.html.twig', [
            'consultations' => $consultations,
            'prescriptions' => $prescriptions,
            'currentFilter' => $filter
        ]);
    }

    #[Route('/ordonnance/create', name: 'app_ordonnance_create', methods: ['POST'])]
    public function createOrdonnance(Request $request, SluggerInterface $slugger): Response
    {
        $consultation = $this->entityManager->getRepository(Consultation::class)
            ->find($request->request->get('consultation_id'));
    
        if (!$consultation) {
            throw $this->createNotFoundException('Consultation not found');
        }
    
        $ordonnance = new Ordonnance();
        $ordonnance->setConsultation($consultation);
        $ordonnance->setDescription($request->request->get('description'));
    
        /** @var UploadedFile $signatureFile */
        $signatureFile = $request->files->get('signature');
    
        if ($signatureFile) {
            $originalFilename = pathinfo($signatureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$signatureFile->guessExtension();
    
            try {
                $signatureFile->move(
                    $this->getParameter('signatures_directory'),
                    $newFilename
                );
    
                // Store only the relative path
                $relativePath = 'uploads/signatures/' . $newFilename;
                $ordonnance->setSignature($relativePath);
                
            } catch (FileException $e) {
                // Handle exception if something happens during file upload
            }
            // Update the 'signature' property to store the file name instead of its contents
        }
    
        $this->entityManager->persist($ordonnance);
        $this->entityManager->flush();
    
        // Return to the portal with a success message
        $this->addFlash('success', 'Prescription created successfully');
        return $this->redirectToRoute('app_medical_dashboard');
    }

    #[Route('/doctor/prescriptions', name: 'app_prescriptions_dashboard')]
    public function prescriptionsDashboard(Request $request): Response
    {
        $filter = $request->query->get('filter', 'all');
        
        // Get all consultations for the current doctor (using hardcoded ID 1 for now)
        $doctor = $this->getUser();
       // $doctor = $this->utilisateurRepository->find(1);
        if (!$doctor) {
            throw $this->createNotFoundException('Doctor with ID 1 not found');
        }

        $consultationsQuery = $this->entityManager->getRepository(Consultation::class)
            ->createQueryBuilder('c')
            ->leftJoin('c.ordonnance', 'o')
            ->where('c.medecin = :doctor')
            ->setParameter('doctor', $doctor);

        // Apply filters
        switch ($filter) {
            case 'today':
                $today = new \DateTime('today');
                $tomorrow = new \DateTime('tomorrow');
                $consultationsQuery->andWhere('c.dateC >= :today AND c.dateC < :tomorrow')
                    ->setParameter('today', $today)
                    ->setParameter('tomorrow', $tomorrow);
                break;
            case 'week':
                $startOfWeek = new \DateTime('monday this week');
                $endOfWeek = new \DateTime('sunday this week 23:59:59');
                $consultationsQuery->andWhere('c.dateC BETWEEN :start AND :end')
                    ->setParameter('start', $startOfWeek)
                    ->setParameter('end', $endOfWeek);
                break;
            case 'month':
                $startOfMonth = new \DateTime('first day of this month midnight');
                $endOfMonth = new \DateTime('last day of this month 23:59:59');
                $consultationsQuery->andWhere('c.dateC BETWEEN :start AND :end')
                    ->setParameter('start', $startOfMonth)
                    ->setParameter('end', $endOfMonth);
                break;
            case 'with_prescription':
                $consultationsQuery->andWhere('o.id IS NOT NULL');
                break;
            case 'without_prescription':
                $consultationsQuery->andWhere('o.id IS NULL')
                    ->andWhere('c.status = :status')
                    ->setParameter('status', 'confirmed');
                break;
        }

        $consultations = $consultationsQuery
            ->orderBy('c.dateC', 'DESC')
            ->getQuery()
            ->getResult();

        $prescriptions = $this->entityManager->getRepository(Ordonnance::class)
            ->findBy(['consultation' => $consultations], ['id' => 'DESC']);

        return $this->render('medical/prescriptions.html.twig', [
            'consultations' => $consultations,
            'prescriptions' => $prescriptions,
            'currentFilter' => $filter
        ]);
    }
    #[Route('/ordonnance/{id}/edit', name: 'app_ordonnance_edit', methods: ['POST'])]
    public function editOrdonnance(Request $request, Ordonnance $ordonnance, SluggerInterface $slugger): Response
    {
        $ordonnance->setDescription($request->request->get('description'));
    
        /** @var UploadedFile $signatureFile */
        $signatureFile = $request->files->get('signature');
    
        if ($signatureFile) {
            $originalFilename = pathinfo($signatureFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$signatureFile->guessExtension();
    
            try {
                $signatureFile->move(
                    $this->getParameter('signatures_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // Handle exception if something happens during file upload
            }
    
            // Update the 'signature' property to store the file name instead of its contents
            $ordonnance->setSignature($this->getParameter('signatures_directory').'/'.$newFilename);
        }
    
        $this->entityManager->flush();
    
        $this->addFlash('success', 'Prescription updated successfully');
        return $this->redirectToRoute('app_prescriptions_dashboard');
    }

    #[Route('/ordonnance/{id}/delete', name: 'app_ordonnance_delete')]
    public function deleteOrdonnance(Ordonnance $ordonnance): Response
    {
        $this->entityManager->remove($ordonnance);
        $this->entityManager->flush();

        $this->addFlash('success', 'Prescription deleted successfully');
        return $this->redirectToRoute('app_prescriptions_dashboard');
    }
}
