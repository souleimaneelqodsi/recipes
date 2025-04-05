<?php
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['role'])) {
    echo json_encode(['success' => false, 'message' => 'Rôle manquant']);
    exit;
}

$role = $input['role'];
$currentUsername = $_SESSION['user']['username'];
$jsonFile = 'users.json';

$users = json_decode(file_get_contents($jsonFile), true);
$found = false;

foreach ($users as &$user) {
    if ($user['username'] === $currentUsername) {
        $user['role'] = $role;
        $_SESSION['user']['role'] = $role;
        $found = true;
        break;
    }
}

if ($found) {
    file_put_contents($jsonFile, json_encode($users, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
}
