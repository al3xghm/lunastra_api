<?php
// Inclure le modèle
include_once 'models/Model.php';

class Controller {
    public function fetchReservations() {
        // Création d'une instance du modèle
        $model = new ReservationModel();

        // Récupérer les données des réservations
        $reservations = $model->getReservations();

        // Vérifier la méthode de la requête
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Décoder les données envoyées par le client (Next.js)
            $data = json_decode(file_get_contents("php://input"));

            // Traiter la réservation (ajouter un ID et sauvegarder dans le fichier JSON)
            $id = $model->addReservation($data);
            
            // Réponse de succès
            echo json_encode([
                'success' => true,
                'message' => 'Réservation effectuée avec succès',
                'data' => [
                    'id' => $id,
                    'prenom' => $data->prenom,
                    'nom' => $data->nom,
                    'email' => $data->email,
                    'date' => $data->date,
                    'horaire' => $data->horaire,
                    'amount' => $data->amount
                ]
            ]);
            exit;
        }

        // Répondre avec les réservations en JSON
        echo json_encode($reservations);
        exit;
    }
}
?>
