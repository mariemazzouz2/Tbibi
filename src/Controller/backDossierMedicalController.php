<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Form\DossierMedicalType;
use App\Repository\DossierMedicalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/back/dossier/medical')]
final class backDossierMedicalController extends AbstractController
{
    #[Route(name: 'app_back_dossier_medical_index', methods: ['GET'])]
    public function backindex(
        DossierMedicalRepository $dossierMedicalRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $query = $dossierMedicalRepository->createQueryBuilder('dm')
            ->getQuery();

        $dossier_medicals = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('back/dossier_medical/index.html.twig', [
            'dossier_medicals' => $dossier_medicals,
        ]);
    }

    // Nouvelle route pour la recherche AJAX
    #[Route('/search', name: 'app_back_dossier_medical_search', methods: ['GET'])]
    public function search(
        DossierMedicalRepository $dossierMedicalRepository,
        Request $request
    ): JsonResponse {
        $searchTerm = $request->query->get('q', '');

        // Requête pour filtrer les dossiers médicaux par terme de recherche
        $queryBuilder = $dossierMedicalRepository->createQueryBuilder('dm')
            ->where('dm.date LIKE :search OR dm.unite LIKE :search OR dm.mesure LIKE :search')
            ->setParameter('search', '%' . $searchTerm . '%')
            ->orderBy('dm.id', 'ASC');

        $dossier_medicals = $queryBuilder->getQuery()->getResult();

        // Préparer les données pour la réponse JSON
        $data = array_map(function (DossierMedical $dossier) {
            return [
                'id' => $dossier->getId(),
                'date' => $dossier->getDate() ? $dossier->getDate()->format('d/m/Y') : 'Non spécifiée',
                'fichier' => $dossier->getFichier(),
                'unite' => $dossier->getUnite() ?? 'Non spécifiée',
                'mesure' => $dossier->getMesure() ?? 'Non spécifiée',
                'utilisateur_image' => $dossier->getUtilisateur() ? $dossier->getUtilisateur()->getImage() : null,
            ];
        }, $dossier_medicals);

        return new JsonResponse($data);
    }

    #[Route('/new', name: 'app_back_dossier_medical_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dossierMedical = new DossierMedical();
        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichier = $form->get('fichier')->getData();
            if ($fichier) {
                $filename = uniqid() . '.' . $fichier->guessExtension();
                $fichier->move($this->getParameter('uploads_directory'), $filename);
                $dossierMedical->setFichier($filename);
            }

            $entityManager->persist($dossierMedical);
            $entityManager->flush();

            return $this->redirectToRoute('app_back_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/dossier_medical/new.html.twig', [
            'dossier_medical' => $dossierMedical,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id}', name: 'app_back_dossier_medical_show', methods: ['GET'])]
    public function backshow(DossierMedical $dossierMedical): Response
    {
        return $this->render('back/dossier_medical/show.html.twig', [
            'dossier_medical' => $dossierMedical,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_back_dossier_medical_edit', methods: ['GET', 'POST'])]
    public function backedit(Request $request, DossierMedical $dossierMedical, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_back_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/dossier_medical/edit.html.twig', [
            'dossier_medical' => $dossierMedical,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_dossier_medical_delete', methods: ['POST'])]
    public function backdelete(Request $request, DossierMedical $dossierMedical, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dossierMedical->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($dossierMedical);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_back_dossier_medical_index', [], Response::HTTP_SEE_OTHER);
    }
}
