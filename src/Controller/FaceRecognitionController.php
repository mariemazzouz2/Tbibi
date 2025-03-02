<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;


class FaceRecognitionController extends AbstractController
{
    private $httpClient;
    private $entityManager;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
    }

    #[Route('/face-login', name: 'face_login')]
    public function index(): Response
    {
        return $this->render('face_login.html.twig');
    }

    #[Route('/api/recognize-face', name: 'api_recognize_face', methods: ['POST'])]
    public function recognizeFace(Request $request): JsonResponse
    {
        $image = $request->files->get('image');

        if (!$image) {
            return new JsonResponse(['error' => 'Aucune image reçue'], 400);
        }

        $imagePath = $image->getPathname();
        $response = $this->httpClient->request('POST', 'http://127.0.0.1:5000/recognize', [
            'body' => [
                'image' => fopen($imagePath, 'r')
            ]
        ]);

        $data = $response->toArray();

        if (isset($data['user_id'])) {
            // Authentification automatique
            $user = $this->entityManager->getRepository(Utilisateur::class)->find($data['user_id']);
            if ($user) {
                return $this->json(['success' => true, 'user' => $user->getEmail()]);
            }
        }

        return $this->json(['error' => 'Utilisateur non reconnu'], 401);
    }
}
