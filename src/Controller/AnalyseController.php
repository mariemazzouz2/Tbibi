<?php

namespace App\Controller;

use App\Entity\Analyse;
use App\Form\AnalyseType;
use App\Repository\AnalyseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Security;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/analyse')]
final class AnalyseController extends AbstractController
{
    #[Route('/{dossierId}', name: 'app_analyse_index', methods: ['GET'])]
    public function index(
        int $dossierId,
        AnalyseRepository $analyseRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        // Créer une requête pour récupérer les analyses filtrées par dossier_id
        $queryBuilder = $analyseRepository->createQueryBuilder('a')
            ->where('a.dossier = :dossierId')
            ->setParameter('dossierId', $dossierId);

        // Paginer les résultats
        $analyses = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('/analyse/index.html.twig', [
            'analyses' => $analyses,
            'dossierId' => $dossierId,
        ]);
    }

    // Nouvelle route pour la recherche AJAX
    #[Route('/search/{dossierId}', name: 'app_analyse_search', methods: ['GET'])]
    public function search(
        int $dossierId,
        AnalyseRepository $analyseRepository,
        Request $request
    ): JsonResponse {
        $searchTerm = $request->query->get('q', '');

        // Requête pour filtrer les analyses par dossierId et terme de recherche
        $queryBuilder = $analyseRepository->createQueryBuilder('a')
            ->where('a.dossier = :dossierId')
            ->setParameter('dossierId', $dossierId)
            ->andWhere('a.type LIKE :search OR a.diagnostic LIKE :search OR a.dateanalyse LIKE :search')
            ->setParameter('search', '%' . $searchTerm . '%')
            ->orderBy('a.id', 'ASC');

        $analyses = $queryBuilder->getQuery()->getResult();

        // Préparer les données pour la réponse JSON
        $data = array_map(function (Analyse $analyse) {
            return [
                'id' => $analyse->getId(),
                'type' => $analyse->getType(),
                'dateanalyse' => $analyse->getDateanalyse() ? $analyse->getDateanalyse()->format('d/m/Y') : 'Non spécifiée',
                'donneesAnalyse' => $analyse->getDonneesAnalyse(),
                'diagnostic' => $analyse->getDiagnostic() ?? 'Non spécifié',
            ];
        }, $analyses);

        return new JsonResponse($data);
    }

    // Les autres méthodes restent inchangées
    #[Route('/new', name: 'app_analyse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, Security $security): Response
    {
        $analyse = new Analyse();
        $form = $this->createForm(AnalyseType::class, $analyse);
        $form->handleRequest($request);

        $user = $security->getUser();
        $dossierId = null;

        if ($user && $user->getDossierMedical()) {
            $dossierMedical = $user->getDossierMedical();
            $analyse->setDossier($dossierMedical);
            $dossierId = $dossierMedical->getId();
        } else {
            $this->addFlash('error', 'Aucun dossier médical trouvé pour cet utilisateur.');
            return $this->redirectToRoute('app_dossier_medical_new');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('donneesAnalyse')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move($this->getParameter('uploads_directory'), $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                }

                $analyse->setDonneesAnalyse($newFilename);
            }

            $entityManager->persist($analyse);
            $entityManager->flush();

            return $this->redirectToRoute('app_analyse_index', ['dossierId' => $dossierId], Response::HTTP_SEE_OTHER);
        }

        return $this->render('analyse/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/show/{id}', name: 'app_analyse_show', methods: ['GET'])]
    public function show(Analyse $analyse): Response
    {
        return $this->render('analyse/show.html.twig', [
            'analyse' => $analyse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_analyse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Analyse $analyse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnalyseType::class, $analyse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_analyse_index', ['dossierId' => $analyse->getDossier()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('analyse/edit.html.twig', [
            'analyse' => $analyse,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_analyse_delete', methods: ['POST'])]
    public function delete(Request $request, Analyse $analyse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$analyse->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($analyse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_analyse_index', ['dossierId' => $analyse->getDossier()->getId()], Response::HTTP_SEE_OTHER);
    }
}
