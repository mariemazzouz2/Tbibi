<?php

namespace App\Controller;
use App\Entity\Produit;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\EmailService;
use Twig\Environment;


#[Route('/commande')]
class CommandeController extends AbstractController
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }
    #[Route( name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        if (!$user) {
            throw $this->createNotFoundException("Aucun utilisateur connecté.");
        }
    
        // Récupérer uniquement les commandes de cet utilisateur
        $commandes = $commandeRepository->findBy(['user' => $user]);
    
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }
    #[Route('/listeAdmin', name: 'app_listeAdmin', methods: ['GET'])]
    public function listeAdmin(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/listeAdmin.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/new/{idProduit}', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ?int $idProduit = null): Response
    {
        $commande = new Commande();

        // Vérifier si un ID de produit est passé et récupérer le produit
        if ($idProduit) {
            $produit = $entityManager->getRepository(Produit::class)->find($idProduit);
            if ($produit) {
                $commande->addProduit($produit);
            }
        }

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commande->calculerMontantTotal();
            $entityManager->persist($commande);
            $entityManager->flush();

            $this->addFlash('success', 'Commande ajoutée avec succès.');

            return $this->redirectToRoute('app_commande_index');
        }

        return $this->render('commande/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(CommandeRepository $commandeRepository, string $id): Response
    {
        $commande = $commandeRepository->find((int) $id); // Conversion en int ici

        if (!$commande) {
            throw $this->createNotFoundException("La commande avec l'ID $id n'existe pas.");
        }

        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager, EmailService $emailService): Response
    {
        // Store original status for comparison
        $originalStatus = $commande->getStatut();
        
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if status has changed
            $newStatus = $commande->getStatut();
            if ($originalStatus !== $newStatus) {
                // Status has changed, send notification email
                $this->sendStatusUpdateEmail($commande, $emailService);
            }
            
            $entityManager->flush();
            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/delete', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commande->getId(), $request->request->get('_token'))) {
            // Remove the association between products and the commande
            foreach ($commande->getProduit() as $produit) {
                $produit->setCommande(null);
                $entityManager->persist($produit);
            }
            
            // Now we can safely remove the commande
            $entityManager->remove($commande);
            $entityManager->flush();
            
            $this->addFlash('success', 'Commande supprimée avec succès.');
        }
    
        return $this->redirectToRoute('app_commande_index');
    }
    #[Route('/{id}/update-status/{status}', name: 'app_commande_update_status', methods: ['GET', 'POST'])]
    public function updateStatus(Commande $commande, string $status, EntityManagerInterface $entityManager, EmailService $emailService): Response
    {
        $originalStatus = $commande->getStatut();
        
        // Update status
        $commande->setStatut($status);
        $entityManager->flush();
        
        // If status changed, send notification
        if ($originalStatus !== $status) {
            $this->sendStatusUpdateEmail($commande, $emailService);
            $this->addFlash('success', 'Statut mis à jour et notification envoyée au client.');
        } else {
            $this->addFlash('info', 'Statut mis à jour.');
        }
        
        return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()]);
    }
    
    /**
     * Helper method to send status update email
     */
    private function sendStatusUpdateEmail(Commande $commande, EmailService $emailService): bool
    {
        $user = $commande->getUser();
        if (!$user || !$user->getEmail()) {
            $this->addFlash('warning', 'Impossible d\'envoyer l\'email : adresse email du client non disponible.');
            return false;
        }
        
        // Get status message based on current status
        $statusMessages = [
            'En cours' => 'est en cours de traitement',
            'Expédiée' => 'a été expédiée et est en route vers vous',
            'Livrée' => 'a été livrée',
            'Annulée' => 'a été annulée'
        ];
        
        $statusMessage = $statusMessages[$commande->getStatut()] ?? 'a été mise à jour';
        
        // Generate invoice URL
        $invoiceUrl = $this->generateUrl('app_facture_show', [
            'id' => $commande->getId()
        ], true); // true = absolute URL
        
        try {
            // Render email templates using Twig
            $htmlContent = $this->twig->render('emails/order_status_update.html.twig', [
                'commande' => $commande,
                'statusMessage' => $statusMessage,
                'user' => $user,
                'invoiceUrl' => $invoiceUrl,
                'currentDate' => new \DateTime()
            ]);
            
            $textContent = strip_tags(str_replace('<br>', "\n", $htmlContent));
            
            // Send email using your direct EmailService
            $emailSent = $emailService->sendEmail(
                $user->getEmail(),
                'Mise à jour de votre commande #' . $commande->getId(),
                $htmlContent,
                $textContent
            );
            
            if (!$emailSent) {
                $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email: ' . $emailService->getLastError());
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération de l\'email: ' . $e->getMessage());
            return false;
        }
    }
    
    #[Route('/{id}/send-notification', name: 'app_commande_send_notification', methods: ['GET'])]
    public function sendNotification(Commande $commande, EmailService $emailService): Response
    {
        $result = $this->sendStatusUpdateEmail($commande, $emailService);
        
        if ($result) {
            $this->addFlash('success', 'Notification envoyée au client avec succès.');
        }
        
        return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()]);
    }
}
