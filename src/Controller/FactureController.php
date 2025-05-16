<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/facture')]
class FactureController extends AbstractController
{
    #[Route('/{id}', name: 'app_facture_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        // Make sure the current user is authorized to see this invoice
        if ($this->getUser() && ($this->getUser() !== $commande->getUser() && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à consulter cette facture.');
        }
        
        return $this->render('facture/show.html.twig', [
            'commande' => $commande,
            'date_emission' => new \DateTime(),
        ]);
    }
    
    #[Route('/{id}/pdf', name: 'app_facture_pdf', methods: ['GET'])]
    public function generatePdf(Commande $commande): Response
    {
        // Make sure the current user is authorized to see this invoice
        if ($this->getUser() && ($this->getUser() !== $commande->getUser() && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à consulter cette facture.');
        }
        
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        // Generate HTML content for PDF
        $html = $this->renderView('facture/pdf.html.twig', [
            'commande' => $commande,
            'date_emission' => new \DateTime(),
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Generate file name
        $fileName = 'Facture_' . $commande->getId() . '_' . date('Y-m-d') . '.pdf';
        
        // Return PDF as response
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]
        );
    }
}