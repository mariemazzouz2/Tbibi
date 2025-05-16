<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class CartItem
{
    private $produit;
    private $quantity;
    
    public function __construct(Produit $produit, int $quantity)
    {
        $this->produit = $produit;
        $this->quantity = $quantity;
    }
    
    public function getproduit(): Produit
    {
        return $this->produit;
    }
    
    public function getQuantity(): int
    {
        return $this->quantity;
    }
    
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }
    
    /**
     * Calculate the total price for this item (price × quantity)
     */
    public function getTotal(): float
    {
        return $this->produit->getPrix() * $this->quantity;
    }
}