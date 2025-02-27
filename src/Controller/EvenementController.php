<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\DemandeParticipation;
use App\Form\Evenement1Type;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

#[Route('/evenement')]
final class EvenementController extends AbstractController
{
    #[Route('/evenements', name: 'app_evenement_index', methods: ['GET'])]
    public function indexBackend(EvenementRepository $evenementRepository): Response
    {
        $evenements = $evenementRepository->findAll();
        
        // Calculer les statistiques
        $totalEvenements = count($evenements);
        $statsParticipation = [];
        $totalParticipations = 0;
        
        foreach ($evenements as $evenement) {
            $nbParticipants = $evenement->getParticipation()->count();
            $totalParticipations += $nbParticipants;
            $statsParticipation[] = [
                'evenement' => $evenement->getTitre(),
                'participants' => $nbParticipants
            ];
        }
        
        // Trier les événements par nombre de participants (du plus grand au plus petit)
        usort($statsParticipation, function($a, $b) {
            return $b['participants'] - $a['participants'];
        });
        
        // Calculer la moyenne de participants par événement
        $moyenneParticipations = $totalEvenements > 0 ? round($totalParticipations / $totalEvenements, 2) : 0;
        
        // Prendre les 5 événements les plus populaires
        $topEvenements = array_slice($statsParticipation, 0, 5);

        return $this->render('evenement/evenement.html.twig', [
            'evenements' => $evenements,
            'statistiques' => [
                'totalEvenements' => $totalEvenements,
                'totalParticipations' => $totalParticipations,
                'moyenneParticipations' => $moyenneParticipations,
                'topEvenements' => $topEvenements
            ]
        ]);
    }
    
    #[Route('/eventfront', name: 'app1_evenementfront_index', methods: ['GET'])]
    public function index1(EvenementRepository $evenementRepository): Response
    {
        $evenements = $evenementRepository->findAll();
        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    
    public function new(Request $request, EntityManagerInterface $entityManager, #[Autowire('%uploads_directory%')] string $uploadDirectory): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(Evenement1Type::class, $evenement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                $newFilename = uniqid('', true) . '.' . $imageFile->guessExtension();
    
                try {
                    // Déplacer le fichier vers le répertoire de téléchargement
                    $imageFile->move($uploadDirectory, $newFilename);
                    $evenement->setImage($newFilename);
                } catch (FileException $e) {
                    // Gestion des erreurs pendant le déplacement du fichier
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image : ' . $e->getMessage());
                    return $this->redirectToRoute('app_evenement_new');
                }
            }
    
            // Persistance de l'objet en base de données
            $entityManager->persist($evenement);
            $entityManager->flush();
    
            $this->addFlash('success', 'Nouvel événement ajouté avec succès !');
            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/evenement/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $demandeParticipation = null;
        
        if ($user) {
            $demandeParticipation = $entityManager->getRepository(DemandeParticipation::class)
                ->findOneBy([
                    'utilisateur' => $user,
                    'evenement' => $evenement
                ]);
        }

        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
            'demandeParticipation' => $demandeParticipation
        ]);
    }

    #[Route('/evenement/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Evenement1Type::class, $evenement);
        $form->handleRequest($request);
    
        dump($evenement, $form); // Ajoutez cette ligne pour déboguer
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/demande-participation', name: 'app_evenement_demande_participation', methods: ['GET'])]
    public function demandeParticipation(Evenement $evenement, EntityManagerInterface $entityManager, Security $security, NotifierInterface $notifier): Response
    {
        $utilisateur = $security->getUser();

        if (!$utilisateur) {
            $this->addFlash('error', 'Vous devez être connecté pour vous inscrire.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si une demande existe déjà
        $demandeExistante = $entityManager->getRepository(DemandeParticipation::class)
            ->findOneBy(['utilisateur' => $utilisateur, 'evenement' => $evenement, 'statut' => 'en_attente']);

        if ($demandeExistante) {
            $this->addFlash('info', 'Vous avez déjà fait une demande pour cet événement.');
            return $this->redirectToRoute('app1_evenementfront_index');
        }

        $demande = new DemandeParticipation();
        $demande->setUtilisateur($utilisateur);
        $demande->setEvenement($evenement);

        $entityManager->persist($demande);
        $entityManager->flush();

        // Notifier l'admin
        $notifier->send(new Notification(
            "Nouvelle demande de participation pour l'événement : " . $evenement->getTitre(),
            ['email']
        ));

        $this->addFlash('success', 'Votre demande de participation a été envoyée à l\'administrateur.');
        return $this->redirectToRoute('app1_evenementfront_index');
    }

    #[Route('/participate/{id}', name: 'app_evenement_participate')]
    public function participate(Evenement $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour participer à un événement.');
        }

        if (!$event->getParticipation()->contains($user)) {
            $event->addParticipation($user);
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'Vous participez maintenant à cet événement !');
        }

        return $this->redirectToRoute('app_evenement_index');
    }

    #[Route('/unparticipate/{id}', name: 'app_evenement_unparticipate')]
    public function unparticipate(Evenement $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour gérer votre participation.');
        }

        if ($event->getParticipation()->contains($user)) {
            $event->removeParticipation($user);
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('info', 'Vous ne participez plus à cet événement.');
        }

        return $this->redirectToRoute('app_evenement_index');
    }

    #[Route('/evenement/calendar', name: 'app_evenement_calendar')]
    public function calendar(EntityManagerInterface $entityManager): Response
    {
        $evenements = $entityManager->getRepository(Evenement::class)->findAll();
        
        $events = [];
        foreach ($evenements as $evenement) {
            $events[] = [
                'title' => $evenement->getTitre(),
                'start' => $evenement->getDateDebut()->format('Y-m-d'),
                'end' => $evenement->getDateFin()->format('Y-m-d'),
                'url' => $this->generateUrl('app_evenement_show', ['id' => $evenement->getId()]),
                'description' => $evenement->getDescription()
            ];
        }

        return $this->render('evenement/calendar.html.twig', [
            'events' => json_encode($events)
        ]);
    }

    #[Route('/events/calendar', name: 'app_events_calendar_public')]
    public function calendarPublic(EntityManagerInterface $entityManager): Response
    {
        $evenements = $entityManager->getRepository(Evenement::class)->findAll();
        
        $events = [];
        foreach ($evenements as $evenement) {
            $events[] = [
                'title' => $evenement->getTitre(),
                'start' => $evenement->getDateDebut()->format('Y-m-d'),
                'url' => $this->generateUrl('app_evenement_show', ['id' => $evenement->getId()]),
                'description' => $evenement->getDescription(),
                'backgroundColor' => '#4e73df',
                'borderColor' => '#4e73df'
            ];
        }

        return $this->render('evenement/calendar_public.html.twig', [
            'events' => json_encode($events)
        ]);
    }
}
