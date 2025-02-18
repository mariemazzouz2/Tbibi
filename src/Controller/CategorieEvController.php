<?php

namespace App\Controller;

use App\Entity\CategorieEv;
use App\Form\CategorieEvType;
use App\Repository\CategorieEvRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categorie/ev')]
final class CategorieEvController extends AbstractController
{
    #[Route(name: 'app_categorie_ev_index', methods: ['GET'])]
    public function index(CategorieEvRepository $categorieEvRepository): Response
    {
        return $this->render('categorie_ev/index.html.twig', [
            'categorie_evs' => $categorieEvRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_categorie_ev_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorieEv = new CategorieEv();
        $form = $this->createForm(CategorieEvType::class, $categorieEv);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorieEv);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_categorie_ev_index', [], Response::HTTP_SEE_OTHER);
        }
    
        // Si le formulaire est soumis mais invalide, renvoyer avec les erreurs
        return $this->render('categorie_ev/new.html.twig', [
            'categorie_ev' => $categorieEv,
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/{id}', name: 'app_categorie_ev_show', methods: ['GET'])]
    public function show(CategorieEv $categorieEv): Response
    {
        return $this->render('categorie_ev/show.html.twig', [
            'categorie_ev' => $categorieEv,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categorie_ev_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategorieEv $categorieEv, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieEvType::class, $categorieEv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_ev_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie_ev/edit.html.twig', [
            'categorie_ev' => $categorieEv,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_ev_delete', methods: ['POST'])]
    public function delete(Request $request, CategorieEv $categorieEv, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorieEv->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorieEv);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categorie_ev_index', [], Response::HTTP_SEE_OTHER);
    }
    public function new1(Request $request): Response
    {
        $categorie = new CategorieEv();
        $form = $this->createForm(CategorieEvType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarde des données si tout est valide
            // Par exemple : $entityManager->persist($categorie);
            // $entityManager->flush();

            return $this->redirectToRoute('app_categorie_ev_index');
        }

        return $this->render('categorie_ev/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
