<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\DemandeParticipation;
use App\Entity\Notification;
use App\Enum\Statut;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackController extends AbstractController
{
    #[Route('/admin', name: 'back')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est connecté et a le rôle ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer tous les événements
        $evenements = $entityManager->getRepository(Evenement::class)->findAll();
        
        // Statistiques de base
        $totalEvenements = count($evenements);
        $statsParticipation = [];
        $totalParticipations = 0;
        $statutsCount = [
            'EN_ATTENTE' => 0,
            'CONFIRME' => 0,
            'TERMINE' => 0
        ];
        
        // Données mensuelles pour le graphique linéaire
        $monthlyData = array_fill(0, 12, 0);
        $monthlyLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        
        foreach ($evenements as $evenement) {
            // Compter les participants
            $nbParticipants = $evenement->getParticipation()->count();
            $totalParticipations += $nbParticipants;
            
            // Statistiques par événement
            $statsParticipation[] = [
                'evenement' => $evenement->getTitre(),
                'participants' => $nbParticipants
            ];
            
            // Compter les statuts
            $statutsCount[$evenement->getStatut()->value]++;
            
            // Données mensuelles
            $mois = $evenement->getDateDebut()->format('n') - 1; // 0-based
            $monthlyData[$mois]++;
        }
        
        // Trier les événements par nombre de participants
        usort($statsParticipation, function($a, $b) {
            return $b['participants'] - $a['participants'];
        });
        
        // Calculer la moyenne de participants par événement
        $moyenneParticipations = $totalEvenements > 0 ? round($totalParticipations / $totalEvenements, 2) : 0;
        
        // Top 5 événements
        $topEvenements = array_slice($statsParticipation, 0, 5);

        // Événements récents (limités à 5)
        $recentEvents = [];
        $evenementsRecents = array_slice($evenements, 0, 5);
        foreach ($evenementsRecents as $event) {
            $recentEvents[] = [
                'titre' => $event->getTitre(),
                'dateDebut' => $event->getDateDebut(),
                'participants' => $event->getParticipation()->count()
            ];
        }

        // Compter les demandes en attente
        $demandesEnAttente = $entityManager->getRepository(DemandeParticipation::class)
            ->count(['statut' => Statut::EN_ATTENTE->value]);

        // Récupérer le nombre de notifications non lues
        $unreadNotificationsCount = $entityManager->getRepository(Notification::class)
            ->count(['isRead' => false]);

        $data = [
            'statistiques' => [
                'totalEvenements' => $totalEvenements,
                'totalParticipations' => $totalParticipations,
                'moyenneParticipations' => $moyenneParticipations,
                'demandesEnAttente' => $demandesEnAttente,
                'topEvenements' => $topEvenements,
                'recentEvents' => $recentEvents,
                'monthlyData' => $monthlyData,
                'monthlyLabels' => $monthlyLabels,
                'statutsCount' => $statutsCount
            ],
            'unreadNotificationsCount' => $unreadNotificationsCount
        ];

        dump($data); // Pour le débogage

        return $this->render('backoffice/dashboard.html.twig', $data);
    }
}
