<?php
// save_post.php

require 'db_config.php';

header('Content-Type: application/json');

// Empfange die JSON-Daten vom Frontend
$data = json_decode(file_get_contents('php://input'));

// Prüfe, ob alle notwendigen Felder vorhanden sind
if (!isset($data->eventId) || !isset($data->eventTitel) || !isset($data->autor) || !isset($data->text)) {
    echo json_encode(['status' => 'error', 'message' => 'Fehlende Daten.']);
    exit;
}

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) { /* Fehlerbehandlung */ }

// Bereite die SQL-Anweisung vor, um alle Daten zu speichern
$stmt = $conn->prepare("INSERT INTO event_news (eventId, eventTitel, autor, email, telefon, text, fotoBase64) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $data->eventId, $data->eventTitel, $data->autor, $data->email, $data->telefon, $data->text, $data->fotoBase64);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Beitrag erfolgreich gespeichert.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Fehler beim Speichern des Beitrags.']);
}

$stmt->close();
$conn->close();
?>