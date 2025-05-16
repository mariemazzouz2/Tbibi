<?php

namespace App\Controller;

use App\Entity\DemandeParticipation;
use App\Entity\Evenement;
use App\Entity\Notification;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport;

class ParticipationController extends AbstractController
{
    private $security;
    private $entityManager;
    private $mailer;
    private const ADMIN_EMAIL = 'jasser.mannai@esprit.tn';

    public function __construct(
        Security $security, 
        EntityManagerInterface $entityManager, 
        MailerInterface $mailer
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    #[Route('/participation/notifications', name: 'app_participation_notifications')]
    public function notifications(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $notifications = $this->entityManager
            ->getRepository(Notification::class)
            ->findUnreadNotifications();

        return $this->render('participation/notifications.html.twig', [
            'notifications' => $notifications
        ]);
    }

    #[Route('/participation/notification/{id}/read', name: 'app_notification_mark_read')]
    public function markNotificationAsRead(Notification $notification): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $notification->setIsRead(true);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_participation_notifications');
    }

    #[Route('/participation/demande/{id}', name: 'app_participation_demande')]
    public function demandeParticipation(Evenement $evenement): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour participer à un événement.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si une demande existe déjà
        $demandeExistante = $this->entityManager->getRepository(DemandeParticipation::class)
            ->findOneBy(['utilisateur' => $user, 'evenement' => $evenement, 'statut' => 'en_attente']);

        if ($demandeExistante) {
            $this->addFlash('info', 'Vous avez déjà fait une demande pour cet événement.');
            return $this->redirectToRoute('app1_evenementfront_index');
        }

        // Créer une nouvelle demande
        $demande = new DemandeParticipation();
        $demande->setUtilisateur($user);
        $demande->setEvenement($evenement);
        $demande->setStatut('en_attente');

        // Créer une notification pour l'administrateur
        $notification = new Notification();
        $notification->setMessage(sprintf(
            'Nouvelle demande de participation pour l\'événement "%s" par %s',
            $evenement->getTitre(),
            $user->getUserIdentifier()
        ));
        $notification->setDemandeParticipation($demande);

        $this->entityManager->persist($demande);
        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $this->addFlash('success', 'Votre demande de participation a été envoyée.');
        return $this->redirectToRoute('app1_evenementfront_index');
    }

    #[Route('/participation/admin/demandes', name: 'app_participation_admin_liste')]
    public function listDemandesAdmin(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $demandes = $this->entityManager->getRepository(DemandeParticipation::class)
            ->findBy(['statut' => 'en_attente'], ['createdAt' => 'DESC']);

        $notifications = $this->entityManager->getRepository(Notification::class)
            ->findUnreadNotifications();

        return $this->render('participation/admin_demandes.html.twig', [
            'demandes' => $demandes,
            'notifications' => $notifications
        ]);
    }

    #[Route('/participation/admin/reponse/{id}/{reponse}', name: 'app_participation_admin_reponse')]
    public function repondreDemande(DemandeParticipation $demande, string $reponse): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Marquer la notification comme lue
        $notification = $this->entityManager->getRepository(Notification::class)
            ->findOneBy(['demandeParticipation' => $demande, 'isRead' => false]);
        
        if ($notification) {
            $notification->setIsRead(true);
        }

        if ($reponse === 'accepter') {
            $demande->setStatut('acceptee');
            
            // Ajouter l'utilisateur à la liste des participants
            $evenement = $demande->getEvenement();
            $evenement->addParticipation($demande->getUtilisateur());
            
            // Envoyer un email à l'utilisateur
            $email = (new Email())
                ->from('jasser.mannai@esprit.tn')
                ->to($demande->getUtilisateur()->getUserIdentifier())
                ->subject('Participation acceptée - ' . $evenement->getTitre())
                ->html(sprintf(
                    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                        <h2 style="color: #4CAF50;">Participation acceptée !</h2>
                        <div style="background-color: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0;">
                            <p>Votre demande de participation à l\'événement "%s" a été acceptée.</p>
                            <h3>Détails de l\'événement :</h3>
                            <ul style="list-style: none; padding: 0;">
                                <li><strong>Date :</strong> du %s au %s</li>
                                <li><strong>Lieu :</strong> %s</li>
                            </ul>
                        </div>
                        <p style="color: #666;">Nous avons hâte de vous y voir !</p>
                    </div>',
                    $evenement->getTitre(),
                    $evenement->getDateDebut()->format('d/m/Y'),
                    $evenement->getDateFin()->format('d/m/Y'),
                    $evenement->getLieu()
                ));
            
            $this->mailer->send($email);
            $this->addFlash('success', 'Demande acceptée et email envoyé à l\'utilisateur.');
        } else {
            $demande->setStatut('refuse');
            
            // Envoyer un email à l'utilisateur
            $email = (new Email())
                ->from('jasser.mannai@esprit.tn')
                ->to($demande->getUtilisateur()->getUserIdentifier())
                ->subject('Participation refusée - ' . $demande->getEvenement()->getTitre())
                ->html(sprintf(
                    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                        <h2 style="color: #f44336;">Participation non acceptée</h2>
                        <div style="background-color: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0;">
                            <p>Nous sommes désolés de vous informer que votre demande de participation à l\'événement "%s" n\'a pas été acceptée.</p>
                        </div>
                        <p style="color: #666;">N\'hésitez pas à participer à d\'autres événements !</p>
                    </div>',
                    $demande->getEvenement()->getTitre()
                ));
            
            $this->mailer->send($email);
            $this->addFlash('info', 'Demande refusée et email envoyé à l\'utilisateur.');
        }

        $this->entityManager->flush();
        return $this->redirectToRoute('app_back');
    }

    #[Route('/participation/reponse/{id}/{action}', name: 'app_participation_reponse')]
    public function repondreDemandeParticipation(DemandeParticipation $demande, string $action): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        try {
            // Trouver la notification associée
            $notification = $this->entityManager->getRepository(Notification::class)
                ->findOneBy(['demandeParticipation' => $demande]);

            if ($notification) {
                $notification->setIsRead(true);
                $this->entityManager->persist($notification);
            }

            // Récupérer l'utilisateur et son email
            $utilisateur = $demande->getUtilisateur();
            if (!$utilisateur) {
                throw new \Exception('Utilisateur non trouvé');
            }

            $userEmail = $utilisateur->getEmail();
            if (!$userEmail) {
                throw new \Exception('Email de l\'utilisateur non trouvé');
            }

            // Préparer le contenu de l'email
            $evenement = $demande->getEvenement();
            $eventTitle = $evenement ? $evenement->getTitre() : 'l\'événement';
            
            if ($action === 'accepter') {
                $demande->setStatut('acceptee');
                $subject = 'Votre demande de participation a été acceptée';
                $htmlContent = "
                    <h2 style='color: #4e73df;'>Demande acceptée</h2>
                    <p>Bonjour {$utilisateur->getNom()},</p>
                    <p>Nous sommes heureux de vous informer que votre demande de participation à <strong>{$eventTitle}</strong> a été acceptée.</p>
                    <p>Vous pouvez maintenant participer à l'événement.</p>
                    <p>Cordialement,<br>L'équipe Tbibi</p>
                ";
            } else {
                $demande->setStatut('refuse');
                $subject = 'Votre demande de participation a été refusée';
                $htmlContent = "
                    <h2 style='color: #e74a3b;'>Demande refusée</h2>
                    <p>Bonjour {$utilisateur->getNom()},</p>
                    <p>Nous sommes désolés de vous informer que votre demande de participation à <strong>{$eventTitle}</strong> a été refusée.</p>
                    <p>Cordialement,<br>L'équipe Tbibi</p>
                ";
            }

            // Créer et envoyer l'email
            $email = (new Email())
                ->from(new Address('mariemazzouz1234@gmail.com', 'Tbibi'))
                ->to($userEmail)
                ->subject($subject)
                ->html($htmlContent);

            // Envoyer l'email
            $this->mailer->send($email);

            // Sauvegarder les changements dans la base de données
            $this->entityManager->persist($demande);
            $this->entityManager->flush();

            $this->addFlash('success', 'Email envoyé avec succès à ' . $userEmail);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_notifications');
    }

    #[Route('/admin/notifications', name: 'app_admin_notifications')]
    public function adminNotifications(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $notifications = $this->entityManager->getRepository(Notification::class)
            ->findBy(['isRead' => false], ['createdAt' => 'DESC']);

        return $this->render('participation/admin_notifications.html.twig', [
            'notifications' => $notifications
        ]);
    }

    #[Route('/participation/notifications/count', name: 'app_notifications_count')]
    public function getNotificationsCount(): Response
    {
        if (!$this->getUser()) {
            return $this->json(['count' => 0]);
        }

        $count = $this->entityManager->getRepository(Notification::class)
            ->count(['isRead' => false]);

        return $this->json(['count' => $count]);
    }

    #[Route('/participation/notifications', name: 'app_notifications_json')]
    public function getNotifications(): Response
    {
        $notifications = $this->entityManager->getRepository(Notification::class)
            ->findBy(['isRead' => false], ['createdAt' => 'DESC']);

        $data = array_map(function($notification) {
            return [
                'id' => $notification->getId(),
                'message' => $notification->getMessage(),
                'createdAt' => $notification->getCreatedAt()->format('c'),
                'demandeParticipation' => [
                    'id' => $notification->getDemandeParticipation()->getId()
                ]
            ];
        }, $notifications);

        return $this->json($data);
    }
}
