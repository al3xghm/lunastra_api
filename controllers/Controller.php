<?php
include_once 'models/Model.php';

class Controller {
    public function fetchReservations() {
        $model = new ReservationModel();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"));
    
            if (isset($data->prenom)) {
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
                if ($data->action == 'delete') {
                    $result = $model->deleteReservation($data->id);
    
                    if ($result) {
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
                    $result = $model->updateReservation($data->id, $data->updatedValues);
    
                    if ($result) {
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
    
        $reservations = $model->getReservations();
    
        echo json_encode($reservations);
        exit;
    }
}
