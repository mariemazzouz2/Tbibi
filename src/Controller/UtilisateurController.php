<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Utilisateur;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\UtilisateurType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Enum\Specialite;
final class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur', name: 'app_utilisateur')]
    public function index(): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[Route('/medecin', name: 'app_medecin')]
    public function medecin(): Response
    {
        return $this->render('utilisateur/medecin.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[Route('/back', name: 'back')]
    public function back(): Response
    {
        return $this->render('utilisateur/backoffice.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[Route('/listeMedecin', name: 'liste_Medecin')]
public function listeMedecin(EntityManagerInterface $entityManager): Response
{
    $utilisateurs = $entityManager->getRepository(Utilisateur::class)->findByRole('ROLE_MEDECIN');

    return $this->render('utilisateur/listeMedecin.html.twig', [
        'utilisateurs' => $utilisateurs,
    ]);
}
#[Route('/listeDemande', name: 'liste_Demande')]
public function listeDemande(EntityManagerInterface $entityManager): Response
{
    $utilisateurs = $entityManager->getRepository(Utilisateur::class)->createQueryBuilder('u')
        ->where('u.roles LIKE :role')
        ->andWhere('u.status = :status')
        ->setParameter('role', '%ROLE_MEDECIN%')
        ->setParameter('status', 0)
        ->getQuery()
        ->getResult();

    return $this->render('utilisateur/listeDemande.html.twig', [
        'utilisateurs' => $utilisateurs,
    ]);
}

#[Route('/listePatient', name: 'liste_Patient')]
public function listePatients(EntityManagerInterface $entityManager): Response
{
    $utilisateurs = $entityManager->getRepository(Utilisateur::class)->findByRole('ROLE_PATIENT');

    return $this->render('utilisateur/listePatient.html.twig', [
        'utilisateurs' => $utilisateurs,
    ]);
}

#[Route('/ajouterMedecin', name: 'ajouter_medecin')]
public function ajouterMedecin(
    Request $request, 
    EntityManagerInterface $entityManager, 
    UserPasswordHasherInterface $passwordHasher
): Response {
    $medecin = new Utilisateur();
    $medecin->setRoles(['ROLE_MEDECIN']);

    $form = $this->createForm(UtilisateurType::class, $medecin, [
        'is_medecin' => true, // Permet d'adapter le formulaire
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Hasher le mot de passe avant d'enregistrer
        $hashedPassword = $passwordHasher->hashPassword($medecin, $medecin->getPassword());
        $medecin->setPassword($hashedPassword);

        // Assurer que les champs taille et poids sont null pour un médecin
        $medecin->setTaille(null);
        $medecin->setPoids(null);

        if ($medecin->getSpecialite()) {
            $medecin->setSpecialite($medecin->getSpecialite());
        }

        $entityManager->persist($medecin);
        $entityManager->flush();

        return $this->redirectToRoute('liste_Medecin');
    }

    return $this->render('utilisateur/formMedecin.html.twig', [
        'form' => $form->createView(),
    ]);
}


#[Route('/ajouterPatient', name: 'ajouter_patient')]
public function ajouterPatient(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{
    $patient = new Utilisateur();
    $patient->setRoles(['ROLE_PATIENT']);

    $form = $this->createForm(UtilisateurType::class, $patient, [
        'is_medecin' => false, // Option pour personnaliser le formulaire
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $hashedPassword = $passwordHasher->hashPassword($patient, $patient->getPassword());
        $patient->setPassword($hashedPassword);
        $entityManager->persist($patient);
        $entityManager->flush();

        return $this->redirectToRoute('liste_Patient');
    }

    return $this->render('utilisateur/formPatient.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/editerPatient/{id}', name: 'editer_patient')]
public function editerPatient(
    int $id, 
    Request $request, 
    EntityManagerInterface $entityManager
): Response {
    // Récupérer le patient en fonction de son ID
    $patient = $entityManager->getRepository(Utilisateur::class)->find($id);

    if (!$patient) {
        throw $this->createNotFoundException('Le patient n\'existe pas.');
    }

    // Créer le formulaire d'édition
    $form = $this->createForm(UtilisateurType::class, $patient, [
        'is_medecin' => false, // Assurez-vous de ne pas afficher les champs spécifiques aux médecins
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Enregistrer les modifications
        $entityManager->flush();

        // Rediriger vers la liste des patients après modification
        return $this->redirectToRoute('liste_Patient');
    }

    return $this->render('utilisateur/editPatient.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/supprimerUtilisateur/{id}', name: 'supprimer_utilisateur')]
public function supprimerUtilisateur(
    int $id, 
    EntityManagerInterface $entityManager
): Response {
    // Récupérer l'utilisateur en fonction de son ID
    $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($id);

    if (!$utilisateur) {
        throw $this->createNotFoundException('L\'utilisateur n\'existe pas.');
    }

    // Supprimer l'utilisateur
    $entityManager->remove($utilisateur);
    $entityManager->flush();

    // Rediriger vers la liste des utilisateurs (médecins ou patients)
    // Vous pouvez ajuster cette redirection en fonction de la page d'où provient la suppression
    if (in_array('ROLE_MEDECIN', $utilisateur->getRoles())) {
        return $this->redirectToRoute('liste_Medecin');
    } else {
        return $this->redirectToRoute('liste_Patient');
    }
}

#[Route('/modifierStatut/{id}', name: 'modifier_statut')]
public function modifierStatut($id, EntityManagerInterface $entityManager): Response
{
    // Récupérer l'utilisateur avec l'ID donné
    $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($id);

    // Vérifier si l'utilisateur existe
    if (!$utilisateur) {
        throw $this->createNotFoundException('Utilisateur non trouvé');
    }

    // Modifier l'attribut status
    $utilisateur->setStatus(1); // Définir le statut à 1

    // Persister les modifications et les enregistrer dans la base de données
    $entityManager->flush();

    // Rediriger ou afficher un message de succès
    return $this->redirectToRoute('liste_Demande');
}
}
