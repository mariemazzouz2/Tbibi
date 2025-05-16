<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index', methods: ['GET'])]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cartService->getCartItems(),
            'total' => $cartService->getTotal(),
            'count' => $cartService->getItemCount(),
        ]);
    }
    
    #[Route('/add/{id}', name: 'app_cart_add', methods: ['GET', 'POST'])]
    public function add(Request $request, Produit $produit, CartService $cartService): Response
    {
        $quantity = (int)$request->request->get('quantity', 1);
        
        if ($quantity < 1) {
            $quantity = 1;
        }
        
        $cartService->add($produit, $quantity);
        
        $this->addFlash('success', sprintf('%s a été ajouté à votre panier.', $produit->getNom()));
        
        // Redirect to referer or to cart index
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        
        return $this->redirectToRoute('app_cart_index');
    }
    
    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['GET'])]
    public function remove(Produit $produit, CartService $cartService): Response
    {
        $cartService->remove($produit->getId());
        
        $this->addFlash('success', sprintf('%s a été retiré de votre panier.', $produit->getNom()));
        
        return $this->redirectToRoute('app_cart_index');
    }
    
    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(Request $request, Produit $produit, CartService $cartService): Response
    {
        $quantity = (int)$request->request->get('quantity', 1);
        
        if ($quantity < 1) {
            $quantity = 1;
        }
        
        $cartService->update($produit->getId(), $quantity);
        
        return $this->redirectToRoute('app_cart_index');
    }
    
    #[Route('/clear', name: 'app_cart_clear', methods: ['GET'])]
    public function clear(CartService $cartService): Response
    {
        $cartService->clear();
        
        $this->addFlash('success', 'Votre panier a été vidé.');
        
        return $this->redirectToRoute('app_cart_index');
    }
    
    #[Route('/checkout', name: 'app_cart_checkout', methods: ['GET'])]
    public function checkout(CartService $cartService): Response
    {
        // Redirect to login if user is not authenticated
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Veuillez vous connecter pour finaliser votre commande.');
            return $this->redirectToRoute('app_login'); // Adjust this route to your login route
        }
        
        // Check if cart is empty
        if (count($cartService->getCartItems()) === 0) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_index');
        }
        
        return $this->render('cart/checkout.html.twig', [
            'items' => $cartService->getCartItems(),
            'total' => $cartService->getTotal(),
        ]);
    }
    #[Route('/confirm', name: 'app_cart_confirm', methods: ['POST'])]
    public function confirm(Request $request, CartService $cartService, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
        // Redirect to login if user is not authenticated
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Veuillez vous connecter pour finaliser votre commande.');
            return $this->redirectToRoute('app_login'); // Adjust this route to your login route
        }
        
        $cartItems = $cartService->getCartItems();
        
        // Check if cart is empty
        if (count($cartItems) === 0) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_index');
        }
        
        // Create a new Commande
        $commande = new Commande();
        $commande->setUser($this->getUser());
        $commande->setDateCommande(new \DateTime());
        $commande->setStatut('En cours');
        
        // Add products to commande - make sure we fetch fresh entities from database
        foreach ($cartItems as $item) {
            $produitId = $item->getProduit()->getId();
            $produit = $produitRepository->find($produitId);
            
            if ($produit) {
                // Add the product to the order for each quantity
                for ($i = 0; $i < $item->getQuantity(); $i++) {
                    $commande->addProduit($produit);
                }
            }
        }
        
        // Calculate total amount
        $commande->calculerMontantTotal();
        
           // Save to database
           $entityManager->persist($commande);
           $entityManager->flush();
           
           // Clear the cart
           $cartService->clear();
           
           // Redirect to the success page
           return $this->redirectToRoute('app_cart_success', ['id' => $commande->getId()]);
       }

    #[Route('/success/{id}', name: 'app_cart_success', methods: ['GET'])]
    public function success(Commande $commande): Response
    {
        // Make sure the current user is authorized to see this order
        if ($this->getUser() !== $commande->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à consulter cette commande.');
        }
        
        return $this->render('cart/success.html.twig', [
            'commande' => $commande,
        ]);
    }
}