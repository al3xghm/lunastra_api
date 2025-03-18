<?php

class ReservationModel {

    private $file = 'data/data.json';

    // Récupérer les réservations depuis le fichier JSON
    public function getReservations() {
        if (file_exists($this->file)) {
            $data = file_get_contents($this->file);
            return json_decode($data, true); // Retourner en tant que tableau associatif
        }
        return [];
    }

    public function updateReservation($id, $updatedValues) {
        $reservations = $this->getReservations();
    
        // Chercher la réservation par son ID
        foreach ($reservations as &$reservation) {
            if ($reservation['id'] == $id) {
                // Mettre à jour toutes les valeurs spécifiées
                foreach ($updatedValues as $key => $value) {
                    if (isset($reservation[$key])) {
                        $reservation[$key] = $value;
                    }
                }
                break;
            }
        }
    
        // Sauvegarder les réservations mises à jour dans le fichier
        return file_put_contents($this->file, json_encode($reservations, JSON_PRETTY_PRINT)) !== false;
    }
    


    // Ajouter une réservation au fichier JSON
    public function addReservation($data) {
        $reservations = $this->getReservations();
        
        // Créer un ID unique pour la réservation
        $newId = max(array_column($reservations, 'id')) + 1; // Trouver l'ID maximum et ajouter 1
        
        $newReservation = [
            'id' => $newId,
            'prenom' => $data->prenom,
            'nom' => $data->nom,
            'email' => $data->email,
            'date' => $data->date,
            'horaire' => $data->horaire,
            'amount' => $data->amount
        ];

        // Ajouter la nouvelle réservation
        $reservations[] = $newReservation;

        // Sauvegarder dans le fichier JSON
        file_put_contents($this->file, json_encode($reservations, JSON_PRETTY_PRINT));

        return $newId;
    }

    // Supprimer une réservation par son ID
    public function deleteReservation($id) {
        $reservations = $this->getReservations();
    
        // Chercher l'index de la réservation à supprimer
        $index = array_search($id, array_column($reservations, 'id'));
        if ($index !== false) {
            // Retirer la réservation du tableau
            unset($reservations[$index]);
            
            // Réindexer le tableau après la suppression
            $reservations = array_values($reservations);

            // Sauvegarder les données mises à jour dans le fichier
            return file_put_contents($this->file, json_encode($reservations, JSON_PRETTY_PRINT)) !== false;
        }
        return false;
    }
}
