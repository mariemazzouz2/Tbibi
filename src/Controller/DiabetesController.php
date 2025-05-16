<?php
namespace App\Controller;

use App\Service\PredictionService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DiabetesController extends AbstractController
{
    #[Route('/diabetes/predicts', name: 'diabetes_predict', methods: ['GET'])]
    public function showForm(): Response
    {
        return $this->render('diabetes/predict.html.twig');
    }

    #[Route('/diabetes/predict', name: 'diabetes_predict_post', methods: ['POST'])]
    public function predict(Request $request, PredictionService $predictionService, LoggerInterface $logger): Response
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        // Débogage : Affiche les données reçues dans les logs
        $logger->info('Données reçues : ' . json_encode($data));

        if ($data === null) {
            return $this->json(['error' => 'Données invalides : ' . json_last_error_msg() . ' Contenu : ' . $content], Response::HTTP_BAD_REQUEST);
        }

        if (!is_array($data)) {
            return $this->json(['error' => 'Données invalides : Pas un tableau'], Response::HTTP_BAD_REQUEST);
        }

        $requiredKeys = ['age', 'hypertension', 'heart_disease', 'bmi', 'HbA1c_level', 'blood_glucose_level', 'gender_Female', 'gender_Male', 'gender_Other', 'smoking_history_No Info', 'smoking_history_current', 'smoking_history_ever', 'smoking_history_former', 'smoking_history_never', 'smoking_history_not current'];
        foreach ($requiredKeys as $key) {
            if (!isset($data[$key])) {
                return $this->json(['error' => "Clé manquante : $key"], Response::HTTP_BAD_REQUEST);
            }
        }

        if (!is_numeric($data['age']) || $data['age'] < 0) {
            return $this->json(['error' => 'Âge invalide'], Response::HTTP_BAD_REQUEST);
        }
        if (!is_numeric($data['hypertension']) || !in_array($data['hypertension'], [0, 1])) {
            return $this->json(['error' => 'Hypertension doit être 0 ou 1'], Response::HTTP_BAD_REQUEST);
        }
        if (!is_numeric($data['heart_disease']) || !in_array($data['heart_disease'], [0, 1])) {
            return $this->json(['error' => 'Maladie cardiaque doit être 0 ou 1'], Response::HTTP_BAD_REQUEST);
        }
        if (!is_numeric($data['bmi']) || $data['bmi'] < 0) {
            return $this->json(['error' => 'IMC invalide'], Response::HTTP_BAD_REQUEST);
        }
        if (!is_numeric($data['HbA1c_level']) || $data['HbA1c_level'] < 0) {
            return $this->json(['error' => 'HbA1c invalide'], Response::HTTP_BAD_REQUEST);
        }
        if (!is_numeric($data['blood_glucose_level']) || $data['blood_glucose_level'] < 0) {
            return $this->json(['error' => 'Glycémie invalide'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier que les colonnes encodées one-hot sont valides (0 ou 1)
        $genderSum = $data['gender_Female'] + $data['gender_Male'] + $data['gender_Other'];
        if (!is_numeric($genderSum) || $genderSum !== 1 || $genderSum < 0) {
            return $this->json(['error' => 'Les colonnes gender doivent contenir exactement une valeur 1 et deux 0'], Response::HTTP_BAD_REQUEST);
        }

        $smokingSum = $data['smoking_history_No Info'] + $data['smoking_history_current'] + $data['smoking_history_ever'] + $data['smoking_history_former'] + $data['smoking_history_never'] + $data['smoking_history_not current'];
        if (!is_numeric($smokingSum) || $smokingSum !== 1 || $smokingSum < 0) {
            return $this->json(['error' => 'Les colonnes smoking_history doivent contenir exactement une valeur 1 et cinq 0'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $prediction = $predictionService->predictDiabetes([
                'age' => $data['age'],
                'hypertension' => $data['hypertension'],
                'heart_disease' => $data['heart_disease'],
                'bmi' => $data['bmi'],
                'HbA1c_level' => $data['HbA1c_level'],
                'blood_glucose_level' => $data['blood_glucose_level'],
                'gender_Female' => $data['gender_Female'],
                'gender_Male' => $data['gender_Male'],
                'gender_Other' => $data['gender_Other'],
                'smoking_history_No Info' => $data['smoking_history_No Info'],
                'smoking_history_current' => $data['smoking_history_current'],
                'smoking_history_ever' => $data['smoking_history_ever'],
                'smoking_history_former' => $data['smoking_history_former'],
                'smoking_history_never' => $data['smoking_history_never'],
                'smoking_history_not current' => $data['smoking_history_not current']
            ]);

            return $this->json(['result' => $prediction['prediction'], 'message' => $prediction['message']]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}