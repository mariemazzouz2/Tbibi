<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
{
    $user = new Utilisateur();
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $plainPassword = $form->get('password')->getData();
        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

        // Gérer le rôle et les champs conditionnels
        $role = $form->get('roles')->getData();
        $user->setRoles([$role]);

        if ($role === 'ROLE_MEDECIN') {
            $specialite = $form->get('specialite')->getData();
            $user->setStatus(0);
        } elseif ($role === 'ROLE_PATIENT') {
            $user->setTaille($form->get('taille')->getData());
            $user->setPoids($form->get('poids')->getData());
            $user->setStatus(1);
        }
        
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_utilisateur');
    }

    return $this->render('registration/register.html.twig', [
        'registrationForm' => $form,
    ]);
}
}
