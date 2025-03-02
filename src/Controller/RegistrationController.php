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
$imageFile = $form->get('image')->getData();
if ($imageFile) {
    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = $slugger->slug($originalFilename);
    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

    try {
        // Déplacer l'image dans le répertoire configuré
        $imageFile->move($this->getParameter('images_directory'), $newFilename);

        // Enregistrer le chemin absolu de l'image dans la base de données
        $absolutePath = $this->getParameter('images_directory') . DIRECTORY_SEPARATOR . $newFilename;
        $user->setImage($absolutePath);  // Enregistrez le chemin absolu dans l'attribut 'image' de l'utilisateur
    } catch (FileException $e) {
        // Gérer l'exception si quelque chose se passe mal lors du téléchargement
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
