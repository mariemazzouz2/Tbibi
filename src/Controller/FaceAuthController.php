<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FaceAuthController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/face-auth', name: 'face_auth')]
    public function index()
    {
        return $this->render('face_auth/index.html.twig');
    }

    #[Route('/face-auth/login', name: 'face_auth_login', methods: ['POST'])]
    public function faceLogin(Request $request)
    {
        $file = $request->files->get('image');

        if (!$file) {
            return new JsonResponse(['success' => false, 'message' => 'Aucune image envoyée'], 400);
        }

        $response = $this->client->request('POST', 'http://127.0.0.1:5000/recognize', [
            'body' => [
                'image' => fopen($file->getPathname(), 'r')
            ]
        ]);

        $data = $response->toArray();

        if ($data['success'] && $data['user'] !== "Inconnu") {
            return new JsonResponse(['success' => true, 'message' => 'Utilisateur reconnu', 'user' => $data['user']]);
        }

        return new JsonResponse(['success' => false, 'message' => 'Utilisateur non reconnu'], 401);
    }
}
