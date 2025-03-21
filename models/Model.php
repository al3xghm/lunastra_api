<?php

class ReservationModel {
    private $file = 'data/data.json';

    private function getDbConnection() {
        $config = require 'config.php';
        try {
            return new PDO("mysql:host={$config['db']['host']};dbname={$config['db']['dbname']}", $config['db']['username'], $config['db']['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    private function createTableIfNotExists() {
        $pdo = $this->getDbConnection();
        $query = "
            CREATE TABLE IF NOT EXISTS reservations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                prenom VARCHAR(255) NOT NULL,
                nom VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                date DATE NOT NULL,
                horaire TIME NOT NULL,
                amount INT NOT NULL
            )";
        $pdo->exec($query);
    }

    public function getReservations() {
        $this->createTableIfNotExists(); 

        $pdo = $this->getDbConnection();
        $stmt = $pdo->query("SELECT * FROM reservations");
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($reservations)) {
            return $this->getReservationsFromFile();
        }

        return $reservations;
    }

    private function getReservationsFromFile() {
        if (file_exists($this->file)) {
            $data = file_get_contents($this->file);
            return json_decode($data, true); 
        }
        return [];
    }

    public function syncJsonToDb() {
        $reservations = $this->getReservationsFromFile();

        foreach ($reservations as $reservation) {
            $pdo = $this->getDbConnection();
            $stmt = $pdo->prepare("SELECT id FROM reservations WHERE id = ?");
            $stmt->execute([$reservation['id']]);
            $existingReservation = $stmt->fetch();

            if (!$existingReservation) {
                $stmt = $pdo->prepare("INSERT INTO reservations (id, prenom, nom, email, date, horaire, amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $reservation['id'],
                    $reservation['prenom'],
                    $reservation['nom'],
                    $reservation['email'],
                    $reservation['date'],
                    $reservation['horaire'],
                    $reservation['amount']
                ]);
            }
        }
    }

    public function syncDbToJson() {
        $pdo = $this->getDbConnection();
        $stmt = $pdo->query("SELECT * FROM reservations");
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        file_put_contents($this->file, json_encode($reservations, JSON_PRETTY_PRINT));
    }

    public function addReservation($data) {
        $this->createTableIfNotExists();
    
        $pdo = $this->getDbConnection();
        $stmt = $pdo->prepare("INSERT INTO reservations (prenom, nom, email, date, horaire, amount) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data->prenom, $data->nom, $data->email, $data->date, $data->horaire, $data->amount]);
    
        $id = $pdo->lastInsertId();
    
$formattedDate = date("d M Y", strtotime($data->date)); 
// Envoi de l'email
$to = $data->email;  
$subject = "Confirmation de votre réservation";

// Construction du message
$message = "
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f9;
                color: #333;
                margin: 0;
                padding: 0;
            }
            .container {
                width: 100%;
                max-width: 600px;
                margin: 20px auto;
                padding: 20px;
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            h2 {
                text-align: center;
            }
            p {
                font-size: 16px;
                line-height: 1.5;
            }
            .details {
                background-color: #f9f9f9;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .details p {
                margin: 5px 0;
            }
            .footer {
                text-align: center;
                font-size: 14px;
                color: #777;
                margin-top: 30px;
            }
            .logo {
                display: block;
                margin: 0 auto;
                max-width: 150px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <img src='https://lunastra-alberteinstein.vercel.app/logo.png' alt='Logo' class='logo'>
            <h2>Confirmation de votre réservation</h2>
            <p>Bonjour {$data->prenom} {$data->nom},</p>
            <p>Nous avons bien enregistré votre réservation pour le <strong>{$formattedDate}</strong> à <strong>{$data->horaire}</strong>.</p>
            <div class='details'>
                <p><strong>Détails de votre réservation :</strong></p>
                <p><strong>Nom :</strong> {$data->prenom} {$data->nom}</p>
                <p><strong>Email :</strong> {$data->email}</p>
                <p><strong>Date :</strong> {$formattedDate}</p>
                <p><strong>Horaire :</strong> {$data->horaire}</p>
                <p><strong>Nombre de places :</strong> {$data->amount}</p>
            </div>
            <p>Merci de votre confiance, et à bientôt!</p>
            <div class='footer'>
                <p>© 2025 Lunastra - Tous droits réservés</p>
            </div>
        </div>
    </body>
    </html>
";

// En-têtes
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
$headers .= "From: no-reply@lunastra.fr";  


    
        // Envoi de l'email
        if (mail($to, $subject, $message, $headers)) {
            echo json_encode([
                'success' => true,
                'message' => 'Réservation effectuée avec succès. Un email de confirmation a été envoyé.',
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
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Réservation effectuée avec succès, mais l\'envoi de l\'email a échoué.',
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
        }
    
        // Sauvegarde dans le fichier JSON
        $reservations = $this->getReservationsFromFile();
        $reservations[] = [
            'id' => $id,
            'prenom' => $data->prenom,
            'nom' => $data->nom,
            'email' => $data->email,
            'date' => $data->date,
            'horaire' => $data->horaire,
            'amount' => $data->amount
        ];
        file_put_contents($this->file, json_encode($reservations, JSON_PRETTY_PRINT));
    
        return $id;
    }
    

    public function updateReservation($id, $updatedValues) {
        $this->createTableIfNotExists(); 

        $pdo = $this->getDbConnection();
        $setParts = [];
        $params = [];

        foreach ($updatedValues as $key => $value) {
            $setParts[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $id;
        
        $stmt = $pdo->prepare("UPDATE reservations SET " . implode(', ', $setParts) . " WHERE id = ?");
        $stmt->execute($params);

        $reservations = $this->getReservationsFromFile();
        foreach ($reservations as &$reservation) {
            if ($reservation['id'] == $id) {
                foreach ($updatedValues as $key => $value) {
                    $reservation[$key] = $value;
                }
                break;
            }
        }
        file_put_contents($this->file, json_encode($reservations, JSON_PRETTY_PRINT));

        return true;
    }

    public function deleteReservation($id) {
        $this->createTableIfNotExists();

        $pdo = $this->getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$id]);

        $reservations = $this->getReservationsFromFile();
        $reservations = array_filter($reservations, function($reservation) use ($id) {
            return $reservation['id'] != $id;
        });
        file_put_contents($this->file, json_encode(array_values($reservations), JSON_PRETTY_PRINT));

        return true;
    }
}
