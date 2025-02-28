<?php
namespace App\Service;

use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationService
{
    private $translator;

    public function __construct()
    {
        // Créer l'objet de traduction
        $this->translator = new GoogleTranslate();
    }

    // Méthode pour traduire le texte
    public function translate(string $text, string $targetLanguage = 'fr'): string
    {
        // Traduire le texte vers la langue cible
        return $this->translator->translate($text, $targetLanguage);
    }
}
