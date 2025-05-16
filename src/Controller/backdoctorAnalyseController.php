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
use Knp\Component\Pager\PaginatorInterface;

#[Route('/backdoctor/analyse')]
final class backdoctorAnalyseController extends AbstractController
{
    #[Route('/{dossierId}', name: 'app_backdoctor_analyse_index', methods: ['GET'], requirements: ['dossierId' => '\d+'])]
    public function index(
        int $dossierId,
        AnalyseRepository $analyseRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        // Créer une requête pour récupérer les analyses filtrées par dossier_id
        $query = $analyseRepository->createQueryBuilder('a')
            ->where('a.dossier = :dossierId')
            ->setParameter('dossierId', $dossierId)
            ->getQuery();

        // Paginer les résultats
        $analyses = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('backdoctor/analyse/index.html.twig', [
            'analyses' => $analyses,
            'dossierId' => $dossierId,
        ]);
    }

    // Nouvelle route pour la recherche AJAX
    #[Route('/search/{dossierId}', name: 'app_backdoctor_analyse_search', methods: ['GET'])]
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

    #[Route('/new', name: 'app_backdoctor_analyse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $analyse = new Analyse();
        $form = $this->createForm(AnalyseType::class, $analyse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($analyse);
            $entityManager->flush();

            return $this->redirectToRoute('app_backdoctor_analyse_index', ['dossierId' => $analyse->getDossier()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backdoctor/analyse/new.html.twig', [
            'analyse' => $analyse,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id}', name: 'app_backdoctor_analyse_show', methods: ['GET'])]
    public function show(Analyse $analyse): Response
    {
        return $this->render('backdoctor/analyse/show.html.twig', [
            'analyse' => $analyse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backdoctor_analyse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Analyse $analyse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnalyseType::class, $analyse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_backdoctor_analyse_index', ['dossierId' => $analyse->getDossier()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backdoctor/analyse/edit.html.twig', [
            'analyse' => $analyse,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_backdoctor_analyse_delete', methods: ['POST'])]
    public function delete(Request $request, Analyse $analyse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$analyse->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($analyse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_backdoctor_analyse_index', ['dossierId' => $analyse->getDossier()->getId()], Response::HTTP_SEE_OTHER);
    }
}
