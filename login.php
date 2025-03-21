<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$config = require 'config.php';

function getDbConnection() {
    global $config;
    
    try {
        return new PDO("mysql:host={$config['db']['host']};dbname={$config['db']['dbname']}", $config['db']['username'], $config['db']['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

function encodeJWT($data) {
    global $config;

    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');

    $payload = json_encode($data);
    $payload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

    $signature = hash_hmac('sha256', "$header.$payload", $config['secret_key'], true);
    $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    return "$header.$payload.$signature";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['username']) && isset($data['password'])) {
        $username = $data['username'];
        $password = $data['password'];

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $payload = [
                'sub' => $user['id'],
                'username' => $user['username'],
                'iat' => time(),
                'exp' => time() + 3600,
            ];

            $jwt = encodeJWT($payload);

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
?>
