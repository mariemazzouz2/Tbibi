<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CartService $cartService
    ) {}
    
    public function createOrderFromCart(Utilisateur $user): ?Commande
    {
        $cartItems = $this->cartService->getCartItems();
        
        if (count($cartItems) === 0) {
            return null;
        }
        
        // Create a new Commande
        $commande = new Commande();
        $commande->setUser($user);
        $commande->setDateCommande(new \DateTime());
        $commande->setStatut('En cours');
        
        // Add products to commande
        foreach ($cartItems as $cartItem) {
            $produit = $cartItem->getproduit();
            for ($i = 0; $i < $cartItem->getQuantity(); $i++) {
                $commande->addProduit($produit);
            }
        }
        
        // Calculate total amount
        $commande->calculerMontantTotal();
        
        // Save to database
        $this->entityManager->persist($commande);
        $this->entityManager->flush();
        
        // Clear the cart
        $this->cartService->clear();
        
        return $commande;
    }
}