<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/produit')]
final class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository, CartService $cartService): Response
    {
        $produits = $produitRepository->findAll();
        
        // Calcul du montant total des produits
        $montantTotal = array_reduce($produits, function ($total, $produit) {
            return $total + $produit->getPrix();
        }, 0);

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'montantTotal' => $montantTotal,
            'cartCount' => count($cartService->getCartItems()),
        ]);
    }
    #[Route('/produitAdmin',name: 'app_produitAdmin', methods: ['GET'])]
    public function produitAdmin(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/produitAdmin.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
         // Calcul du montant total des produits
         $montantTotal = array_reduce($produits, function ($total, $produit) {
            return $total + $produit->getPrix();
        }, 0);

        return $this->render('produit/produitAdmin.html.twig', [
            'produits' => $produits,
            'montantTotal' => $montantTotal,
        ]);
    }
    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $imageFile = $form->get('imageFile')->getData();
            
            // If an image was uploaded
            if ($imageFile) {
                // Generate a safe filename
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                    
                    // Set the image property to the filename
                    $produit->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image: ' . $e->getMessage());
                    return $this->renderForm('produit/new.html.twig', [
                        'produit' => $produit,
                        'form' => $form,
                    ]);
                }
            } else {
                // No image was uploaded, add an error
                $this->addFlash('error', 'L\'image est obligatoire.');
                return $this->renderForm('produit/new.html.twig', [
                    'produit' => $produit,
                    'form' => $form,
                ]);
            }
            
            // Save the product to database
            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été créé avec succès.');
            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
