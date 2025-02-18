<?php

namespace App\Controller;
use App\Entity\Produit;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'app_commande_index', methods: ['GET'])]
public function index(CommandeRepository $commandeRepository): Response
{
    return $this->render('commande/index.html.twig', [
        'commandes' => $commandeRepository->findAll(),
    ]);
}
#[Route('/listeAdmin', name: 'app_listeAdmin', methods: ['GET'])]
public function listeAdmin(CommandeRepository $commandeRepository): Response
{
    return $this->render('commande/listeAdmin.html.twig', [
        'commandes' => $commandeRepository->findAll(),
    ]);
}

#[Route('/new/{idProduit}', name: 'app_commande_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, ?int $idProduit = null): Response
{
    $commande = new Commande();

    // Vérifier si un ID de produit est passé et récupérer le produit
    if ($idProduit) {
        $produit = $entityManager->getRepository(Produit::class)->find($idProduit);
        if ($produit) {
            $commande->addProduit($produit);
        }
    }

    $form = $this->createForm(CommandeType::class, $commande);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $commande->calculerMontantTotal();
        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Commande ajoutée avec succès.');

        return $this->redirectToRoute('app_commande_index');
    }

    return $this->render('commande/form.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/commande/{id}', name: 'app_commande_show', methods: ['GET'])]
public function show(CommandeRepository $commandeRepository, string $id): Response
{
    $commande = $commandeRepository->find((int) $id); // Conversion en int ici

    if (!$commande) {
        throw $this->createNotFoundException("La commande avec l'ID $id n'existe pas.");
    }

    return $this->render('commande/show.html.twig', [
        'commande' => $commande,
    ]);
}

    
    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commande->calculerMontantTotal();
            $entityManager->flush();
            $this->addFlash('success', 'Commande mise à jour avec succès.');

            return $this->redirectToRoute('commande_index');
        }

        return $this->render('commande/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
            $this->addFlash('success', 'Commande supprimée avec succès.');
        }

        return $this->redirectToRoute('commande_index');
    }
}
