<?php
// Permettre l'accès depuis n'importe quelle origine
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


// Inclure les fichiers nécessaires
include_once 'controllers/Controller.php';

$controller = new Controller();
$controller->fetchReservations();
?>
