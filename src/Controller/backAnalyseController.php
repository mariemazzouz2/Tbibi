<?php

namespace App\Controller;

use App\Entity\Analyse;
use App\Form\AnalyseType;
use App\Repository\AnalyseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/back/analyse')]
final class backAnalyseController extends AbstractController
{
    #[Route(name: 'backapp_analyse_index', methods: ['GET'])]
    public function backindex(AnalyseRepository $analyseRepository): Response
    {
        return $this->render('back/analyse/index.html.twig', [
            'analyses' => $analyseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'backapp_analyse_new', methods: ['GET', 'POST'])]
    public function backnew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $analyse = new Analyse();
        $form = $this->createForm(AnalyseType::class, $analyse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($analyse);
            $entityManager->flush();

            return $this->redirectToRoute('app_analyse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/analyse/new.html.twig', [
            'analyse' => $analyse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'backapp_analyse_show', methods: ['GET'])]
    public function backshow(Analyse $analyse): Response
    {
        return $this->render('back/analyse/show.html.twig', [
            'analyse' => $analyse,
        ]);
    }

    #[Route('/{id}/edit', name: 'backapp_analyse_edit', methods: ['GET', 'POST'])]
    public function backedit(Request $request, Analyse $analyse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnalyseType::class, $analyse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_analyse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/analyse/edit.html.twig', [
            'analyse' => $analyse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'backapp_analyse_delete', methods: ['POST'])]
    public function backdelete(Request $request, Analyse $analyse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$analyse->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($analyse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_analyse_index', [], Response::HTTP_SEE_OTHER);
    }
}
