<?php
// save_post.php

// 1. Deine Datenbank-Zugangsdaten
// WICHTIG: Ändere dein Passwort, falls noch nicht geschehen!
$servername = "database-5018680472.webspace-host.com";      // Diesen Wert bekommst du von deinem Hoster
$username   = "dbu1596664";
$password   = "Focke2212#"; // <-- Trage hier dein NEUES, sicheres Passwort ein!
$dbname     = "dbs14794189";

header('Content-Type: application/json');

// 2. Daten vom Frontend empfangen
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

if (!isset($data->eventId) || !isset($data->autor) || !isset($data->text)) {
    echo json_encode(['status' => 'error', 'message' => 'Fehlende Daten.']);
    exit;
}

// 3. Mit der Datenbank verbinden
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Datenbankverbindung fehlgeschlagen.']);
    exit;
}

// 4. Daten sicher in die Datenbank einfügen
$stmt = $conn->prepare("INSERT INTO event_news (eventId, autor, email, telefon, text, fotoBase64) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $data->eventId, $data->autor, $data->email, $data->telefon, $data->text, $data->fotoBase64);

$response = [];
if ($stmt->execute()) {
    $response['status'] = 'success';
    $response['message'] = 'Beitrag erfolgreich gespeichert.';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Fehler beim Speichern in der Datenbank.';
}

echo json_encode($response);

$stmt->close();
$conn->close();
?>