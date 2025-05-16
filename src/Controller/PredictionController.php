<?php
namespace App\Controller;

use App\Entity\Prediction;
use App\Form\PredictionType;
use App\Service\PredictionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/prediction')]
class PredictionController extends AbstractController
{
    #[Route('/new', name: 'app_prediction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PredictionService $predictionService): Response
    {
        $prediction = new Prediction();
        $form = $this->createForm(PredictionType::class, $prediction);
        $form->handleRequest($request);

        $result = null;
        $utilisateur = $prediction->getDossier()?->getUtilisateur();
        $dateNaissance = $utilisateur?->getDateNaissance();

        // Définir le genre de manière explicite
        if ($utilisateur?->getSexe() == 'Homme')
            $gender = "Male";
        else
            $gender = "Female";

        // Calculer l'âge
        $age = $dateNaissance ? $dateNaissance->diff(new \DateTime())->y : 0;

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            $data = $form->getData();

            // Transformation des données
            $features = $this->prepareFeatures($data, $gender);

            // Appel du service de prédiction
            $result = $predictionService->predictDiabetes($features);

            // Sauvegarde en base si nécessaire
            $entityManager->persist($prediction);
            $entityManager->flush();

            $this->addFlash('success', 'Prédiction effectuée avec succès.');
            return $this->redirectToRoute('show_prediction_result', [
                'predictionResult' => $result,
            ]);
        }

        return $this->render('prediction/new.html.twig', [
            'form' => $form->createView(),
            'predictionResult' => $result, // Passer le résultat à la vue
        ]);
    }

    #[Route('/prediction/result', name: 'show_prediction_result')]
    public function showPredictionResult(Request $request): Response
    {
        // Récupérer les résultats de la prédiction envoyés via la redirection
        $predictionResult = $request->query->get('predictionResult');

        return $this->render('prediction/show_prediction_result.html.twig', [
            'predictionResult' => $predictionResult,
        ]);
    }
    private function prepareFeatures(Prediction $data, string $gender): array
    {
        $utilisateur = $data->getDossier()?->getUtilisateur();
        $dateNaissance = $utilisateur?->getDateNaissance();

        // Transformation des données
        $genderArray = $this->transformGender($gender);  // Assurez-vous que gender est bien transformé

        // Calcul de l'âge
        $age = $dateNaissance ? $dateNaissance->diff(new \DateTime())->y : 0;

        return [
            'age' => $age,
            'hypertension' => $this->transformBoolean($data->isHypertension()),
            'heart_disease' => $this->transformBoolean($data->isHeartDisease()),
            'bmi' => $data->getBmi() ?? 0.0,
            'HbA1c_level' => $data->getHbA1cLevel() ?? 0.0,
            'blood_glucose_level' => $data->getBloodGlucoseLevel() ?? 0.0,
            'gender_Female' => $genderArray['Female'],  // Utilisation correcte de la clé 'Female'
            'gender_Male' => $genderArray['Male'],      // Utilisation correcte de la clé 'Male'
            'gender_Other' => $genderArray['Other'],    // Utilisation correcte de la clé 'Other'
            'smoking_history_No Info' => $this->transformSmokingHistory($data->getSmokingHistory() ?? '')['No Info'],
            'smoking_history_current' => $this->transformSmokingHistory($data->getSmokingHistory() ?? '')['current'],
            'smoking_history_ever' => $this->transformSmokingHistory($data->getSmokingHistory() ?? '')['ever'],
            'smoking_history_former' => $this->transformSmokingHistory($data->getSmokingHistory() ?? '')['former'],
            'smoking_history_never' => $this->transformSmokingHistory($data->getSmokingHistory() ?? '')['never'],
            'smoking_history_not current' => $this->transformSmokingHistory($data->getSmokingHistory() ?? '')['not current'],
        ];
    }

    private function transformSmokingHistory(string $smokingHistory): array
    {
        return [
            'No Info' => $smokingHistory === 'No Info' ? 1 : 0,
            'current' => $smokingHistory === 'current' ? 1 : 0,
            'ever' => $smokingHistory === 'ever' ? 1 : 0,
            'former' => $smokingHistory === 'former' ? 1 : 0,
            'never' => $smokingHistory === 'never' ? 1 : 0,
            'not current' => $smokingHistory === 'not current' ? 1 : 0,
        ];
    }

    private function transformGender(string $gender): array
    {
        return [
            'Female' => $gender === 'Female' ? 1 : 0,
            'Male' => $gender === 'Male' ? 1 : 0,
            'Other' => $gender === 'Other' ? 1 : 0,
        ];
    }

    private function transformBoolean($value): int
    {
        return $value ? 1 : 0;
    }
}
