<?php

namespace App\Service;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use App\Entity\CartItem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{ private $session;
    private $produitRepository;
    
    /**
     * CartService constructor.
     */
    public function __construct(
        RequestStack $requestStack,
        ProduitRepository $produitRepository
    ) {
        $this->session = $requestStack->getSession();
        $this->produitRepository = $produitRepository;
    }
    
   // ... existing properties and constructor

    /**
     * Add a product to the cart
     * @param Produit|int $product Product object or product ID
     * @param int $quantity Quantity to add
     */
    public function add($product, int $quantity = 1): void
    {
        // Get the product ID whether a Produit object or an int was passed
        $id = ($product instanceof Produit) ? $product->getId() : (int) $product;
        
        $cart = $this->session->get('cart', []);
        
        // Check if $cart[$id] exists, and ensure it's an integer
        if (isset($cart[$id])) {
            // If it's an array or another type, convert to int or set to 0
            if (is_array($cart[$id])) {
                $cart[$id] = 0; // Reset to 0 if it's an array
            } else {
                $cart[$id] = (int)$cart[$id]; // Convert to integer
            }
            $cart[$id] += $quantity;
        } else {
            $cart[$id] = $quantity;
        }
        
        $this->session->set('cart', $cart);
    }
    
    /**
     * Remove a product from the cart
     * @param Produit|int $product Product object or product ID
     */
    public function remove($product): void
    {
        // Get the product ID whether a Produit object or an int was passed
        $id = ($product instanceof Produit) ? $product->getId() : (int) $product;
        
        $cart = $this->session->get('cart', []);
        
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }
        
        $this->session->set('cart', $cart);
    }
    
    /**
     * Update product quantity in cart
     * @param Produit|int $product Product object or product ID
     * @param int $quantity New quantity
     */
    public function update($product, int $quantity): void
    {
        // Get the product ID whether a Produit object or an int was passed
        $id = ($product instanceof Produit) ? $product->getId() : (int) $product;
        
        $cart = $this->session->get('cart', []);
        
        if ($quantity <= 0) {
            $this->remove($id);
        } else {
            $cart[$id] = $quantity;
            $this->session->set('cart', $cart);
        }
    }
    
    
    /**
     * Clear the entire cart
     */
    public function clear(): void
    {
        $this->session->remove('cart');
    }
    
    /**
     * Get the raw cart data
     */
    public function getCart(): array
    {
        return $this->session->get('cart', []);
    }
    
    /**
     * Get cart items with product details
     * @return CartItem[]
     */
    public function getCartItems(): array
    {
        $cart = $this->getCart();
        $cartItems = [];
        
        foreach ($cart as $id => $quantity) {
            $product = $this->produitRepository->find($id);
            
            if ($product) {
                // Fix: Make sure $quantity is an integer
                $cartItems[] = new CartItem($product, (int)$quantity);
            }
        }
        
        return $cartItems;
    }
    
    /**
     * Calculate the total price of the cart
     */
    public function getTotal(): float
    {
        $total = 0;
        $cartItems = $this->getCartItems();
        
        foreach ($cartItems as $item) {
            // Properly calculate by multiplying price by quantity
            $total += $item->getproduit()->getPrix() * $item->getQuantity();
        }
        
        return $total;
    }
    
    /**
     * Get the number of items in the cart
     */
    public function getItemCount(): int
    {
        $cart = $this->getCart();
        $count = 0;
        
        foreach ($cart as $quantity) {
            $count += (int)$quantity;
        }
        
        return $count;
    }
}