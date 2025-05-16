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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
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
            $user->setSpecialite($specialite);
        
            $diplomeFile = $form->get('diplome')->getData();
            if ($diplomeFile) {
                $newFilename = uniqid().'.'.$diplomeFile->guessExtension();
                $diplomeFile->move($this->getParameter('diplomes_directory'), $newFilename);
                $user->setDiplome($newFilename);
            }
        
            $user->setStatus(0);
        } elseif ($role === 'ROLE_PATIENT') {
            $user->setTaille($form->get('taille')->getData());
            $user->setPoids($form->get('poids')->getData());
            $user->setStatus(1);
        }
        // Gestion de l'image de profil
        // Gérer l'image de profil
// Gestion de l'image de profil
$imageFile = $form->get('image')->getData();
if ($imageFile) {
    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = $slugger->slug($originalFilename);
    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

    try {
        // Déplacer l'image dans le répertoire public
        $imageFile->move($this->getParameter('images_directory'), $newFilename);

        // Copier l'image dans le second répertoire
        $destinationPath = $this->getParameter('known_faces_directory') . DIRECTORY_SEPARATOR . $newFilename;
        copy($this->getParameter('images_directory') . DIRECTORY_SEPARATOR . $newFilename, $destinationPath);

        // Enregistrer le chemin absolu dans la base de données
        $user->setImage($this->getParameter('images_directory') . DIRECTORY_SEPARATOR . $newFilename);
    } catch (FileException $e) {
        // Gérer l'exception en cas d'erreur
    }
}
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_login');
    }

    return $this->render('registration/register.html.twig', [
        'registrationForm' => $form,
    ]);
}
}
