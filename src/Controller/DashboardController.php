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
        $conn = $entityManager->getConnection();
        
        // Total des événements
        $totalEvenements = $entityManager->getRepository(Evenement::class)->count([]);
        
        // Total des participations acceptées
        $sql = '
            SELECT COUNT(*) as total 
            FROM demande_participation 
            WHERE statut = :statut
        ';
        $totalParticipations = (int)$conn->executeQuery($sql, ['statut' => 'acceptee'])->fetchOne();
        
        // Moyenne de participants par événement
        $moyenneParticipants = $totalEvenements > 0 ? round($totalParticipations / $totalEvenements, 1) : 0;
        
        // Top 5 des événements les plus populaires
        $sql = '
            SELECT e.titre, COUNT(dp.id) as total_participants
            FROM evenement e
            LEFT JOIN demande_participation dp ON e.id = dp.evenement_id
            WHERE dp.statut = :statut
            GROUP BY e.id, e.titre
            ORDER BY total_participants DESC
            LIMIT 5
        ';
        $topEvenements = $conn->executeQuery($sql, ['statut' => 'acceptee'])->fetchAllAssociative();

        // Données pour le graphique d'évolution des participations
        $sql = '
            SELECT MONTH(e.date_debut) as mois, COUNT(dp.id) as participations
            FROM evenement e
            LEFT JOIN demande_participation dp ON e.id = dp.evenement_id
            WHERE dp.statut = :statut
            GROUP BY MONTH(e.date_debut)
            ORDER BY mois ASC
        ';
        $evolutionParticipations = $conn->executeQuery($sql, ['statut' => 'acceptee'])->fetchAllAssociative();

        // Formater les données pour le graphique
        $labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        $data = array_fill(0, 12, 0); // Initialiser avec des zéros

        foreach ($evolutionParticipations as $stat) {
            $moisIndex = (int)$stat['mois'] - 1;
            $data[$moisIndex] = (int)$stat['participations'];
        }

        // Debug des données
        $debug = [
            'totalParticipations' => $totalParticipations,
            'evolutionParticipations' => $evolutionParticipations,
            'topEvenements' => $topEvenements,
            'sql' => $sql
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
            'debug' => $debug
        ]);
    }
}
