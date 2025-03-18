<?php
// Inclure le modèle
include_once 'models/Model.php';

class Controller {
    public function fetchReservations() {
        // Création d'une instance du modèle
        $model = new ReservationModel();
    
        // Vérifier la méthode de la requête
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Décoder les données envoyées par le client (Next.js)
            $data = json_decode(file_get_contents("php://input"));
    
            // Si des données de réservation sont envoyées (création de réservation)
            if (isset($data->prenom)) {
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
            } elseif (isset($data->id) && isset($data->action)) {
                // Gérer la suppression et la mise à jour selon l'action
                if ($data->action == 'delete') {
                    // Suppression de la réservation
                    $result = $model->deleteReservation($data->id);
    
                    if ($result) {
                        // Réponse de succès pour la suppression
                        echo json_encode([
                            'success' => true,
                            'message' => 'Réservation supprimée avec succès',
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Erreur lors de la suppression de la réservation',
                        ]);
                    }
                } elseif ($data->action == 'update' && isset($data->updatedValues)) {
                    // Mise à jour de la réservation
                    $result = $model->updateReservation($data->id, $data->updatedValues);
    
                    if ($result) {
                        // Réponse de succès pour la mise à jour
                        echo json_encode([
                            'success' => true,
                            'message' => 'Réservation mise à jour avec succès',
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Erreur lors de la mise à jour de la réservation',
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Action non reconnue ou données manquantes',
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Données manquantes pour la requête',
                ]);
            }
            exit;
        }
    
        // Récupérer les données des réservations
        $reservations = $model->getReservations();
    
        // Répondre avec les réservations en JSON
        echo json_encode($reservations);
        exit;
    }
}
