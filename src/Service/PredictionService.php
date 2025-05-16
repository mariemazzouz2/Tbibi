<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PredictionService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function predictDiabetes(array $data): string
{
    $response = $this->client->request(
        'POST',
        'http://localhost:5000/predict',
        [
            'json' => $data
        ]
    );

    // Convertir la réponse JSON en tableau associatif
    $responseData = $response->toArray();

    // Vérifier si la clé 'prediction' existe dans la réponse et retourner sa valeur en string
    if (isset($responseData['prediction'])) {
        return (string) $responseData['prediction'];  // Retourner la valeur de la prédiction en string
    }

    // Si 'prediction' n'est pas trouvé, retourner un message d'erreur ou un résultat par défaut
    return 'Erreur : Prédiction non disponible';
}

}