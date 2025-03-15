<?php

class ReservationModel {

    private $file = 'data/data.json';

    // Récupérer les réservations depuis le fichier JSON
    public function getReservations() {
        if (file_exists($this->file)) {
            $data = file_get_contents($this->file);
            return json_decode($data, true);
        }
        return [];
    }

    // Ajouter une réservation au fichier JSON
    public function addReservation($data) {
        $reservations = $this->getReservations();
        
        // Créer un ID unique pour la réservation
        $newId = count($reservations) + 1;
        
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
        file_put_contents($this->file, json_encode($reservations));

        return $newId;
    }
}
?>
