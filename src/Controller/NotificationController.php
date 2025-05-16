<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\DemandeParticipation;
use App\Enum\Statut;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'app_admin_notifications')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Récupérer toutes les notifications non lues
        $notifications = $entityManager->getRepository(Notification::class)
            ->findBy(['isRead' => false], ['createdAt' => 'DESC']);

        // Mettre à jour le compteur en session
        $request->getSession()->set('unread_notifications_count', count($notifications));

        return $this->render('participation/admin_notifications.html.twig', [
            'notifications' => $notifications
        ]);
    }

    #[Route('/participation/{id}/{action}', name: 'app_participation_reponse')]
    public function repondreDemandeParticipation(
        DemandeParticipation $demandeParticipation,
        string $action,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Mettre à jour le statut de la demande
        if ($action === 'accepter') {
            $demandeParticipation->setStatut(Statut::CONFIRME->value);
            $message = 'La demande de participation a été confirmée.';
        } elseif ($action === 'refuser') {
            $demandeParticipation->setStatut(Statut::ANNULE->value);
            $message = 'La demande de participation a été annulée.';
        } else {
            throw $this->createNotFoundException('Action non valide');
        }

        // Marquer la notification comme lue
        $notification = $entityManager->getRepository(Notification::class)
            ->findOneBy(['demandeParticipation' => $demandeParticipation]);
        
        if ($notification) {
            $notification->setIsRead(true);
            
            // Mettre à jour le compteur en session
            $unreadCount = $request->getSession()->get('unread_notifications_count', 0);
            if ($unreadCount > 0) {
                $request->getSession()->set('unread_notifications_count', $unreadCount - 1);
            }
        }

        $entityManager->flush();

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_admin_notifications');
    }
}
