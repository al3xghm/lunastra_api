


<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

define('SECRET_KEY', 'luneinstein');

function encodeJWT($data) {
    // Encoder le header
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');

    // Encoder le payload
    $payload = json_encode($data);
    $payload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

    // Générer la signature
    $signature = hash_hmac('sha256', "$header.$payload", SECRET_KEY, true);
    $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    // Retourner le JWT complet
    return "$header.$payload.$signature";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['username']) && isset($data['password'])) {
        $username = $data['username'];
        $password = $data['password'];

        $user = ['id' => 1, 'username' => 'admin', 'password' => password_hash('admin', PASSWORD_DEFAULT)];

        if ($user['username'] == $username && password_verify($password, $user['password'])) {
            // Créer le payload pour le JWT
            $payload = [
                'sub' => $user['id'],
                'username' => $user['username'],
                'iat' => time(),
                'exp' => time() + 3600, // Expiration dans 1 heure
            ];

            // Générer le JWT
            $jwt = encodeJWT($payload);

            // Retourner le JWT
            echo json_encode(['status' => 'success', 'token' => $jwt]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Identifiants incorrects']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Les données de connexion sont manquantes']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée']);
}
