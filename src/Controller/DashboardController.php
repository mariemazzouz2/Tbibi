<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\DemandeParticipation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/back', name: 'app_back')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/api/dashboard/stats', name: 'app_dashboard_stats')]
    public function getStats(EntityManagerInterface $entityManager): JsonResponse
    {
        // Statistiques des événements
        $totalEvenements = $entityManager->getRepository(Evenement::class)->count([]);
        
        // Total des participations (participations directes + demandes acceptées)
        $conn = $entityManager->getConnection();
        
        // Compter les participations directes (table participe)
        $sqlParticipations = '
            SELECT COUNT(*) as total 
            FROM participe
        ';
        $participationsDirectes = (int)$conn->fetchOne($sqlParticipations);
        
        // Compter les demandes acceptées
        $demandesAcceptees = $entityManager->getRepository(DemandeParticipation::class)
            ->count(['statut' => 'acceptee']);
            
        $totalParticipations = $participationsDirectes + $demandesAcceptees;
        
        // Moyenne de participants par événement
        $moyenneParticipants = $totalEvenements > 0 ? round($totalParticipations / $totalEvenements, 1) : 0;
        
        // Top 5 des événements les plus populaires
        $sql = '
            SELECT e.titre, 
                   (SELECT COUNT(*) FROM participe p WHERE p.evenement_id = e.id) +
                   COALESCE((SELECT COUNT(*) FROM demande_participation dp WHERE dp.evenement_id = e.id AND dp.statut = \'acceptee\'), 0)
                   as total_participants
            FROM evenement e
            GROUP BY e.id, e.titre
            ORDER BY total_participants DESC
            LIMIT 5
        ';
        $topEvenements = $conn->fetchAllAssociative($sql);

        // Données pour le graphique d'évolution des participations
        $sql = '
            SELECT MONTH(e.date_debut) as mois,
                   (SELECT COUNT(*) FROM participe p WHERE p.evenement_id = e.id) +
                   COALESCE((SELECT COUNT(*) FROM demande_participation dp WHERE dp.evenement_id = e.id AND dp.statut = \'acceptee\'), 0)
                   as participations
            FROM evenement e
            GROUP BY MONTH(e.date_debut)
            ORDER BY mois ASC
        ';
        $evolutionParticipations = $conn->fetchAllAssociative($sql);

        // Formater les données pour le graphique
        $labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        $data = array_fill(0, 12, 0); // Initialiser avec des zéros

        foreach ($evolutionParticipations as $stat) {
            $moisIndex = (int)$stat['mois'] - 1;
            $data[$moisIndex] = (int)$stat['participations'];
        }

        // Debug des données
        $debug = [
            'participationsDirectes' => $participationsDirectes,
            'demandesAcceptees' => $demandesAcceptees,
            'evolutionParticipations' => $evolutionParticipations,
            'topEvenements' => $topEvenements
        ];
        
        return new JsonResponse([
            'totalEvenements' => $totalEvenements,
            'totalParticipations' => $totalParticipations,
            'moyenneParticipants' => $moyenneParticipants,
            'topEvenements' => array_map(function($event) {
                return [
                    'titre' => $event['titre'],
                    'participations' => (int)$event['total_participants']
                ];
            }, $topEvenements),
            'chartLabels' => $labels,
            'chartData' => array_values($data),
            'debug' => $debug // Ajout des données de debug
        ]);
    }
}
