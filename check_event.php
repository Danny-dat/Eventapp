<?php


// Lade die Datenbank-Konfiguration
require 'db_config.php';

// Setze den Antwort-Typ auf JSON
header('Content-Type: application/json');

// Hole die eventId aus der URL (?eventId=...)
$eventId = $_GET['eventId'] ?? '';

if (empty($eventId)) {
    echo json_encode(['status' => 'error', 'message' => 'Keine Event-ID übergeben.']);
    exit;
}

// Stelle eine Verbindung zur Datenbank her
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Datenbankverbindung fehlgeschlagen.']);
    exit;
}

// Frage die Daten für das spezifische Event ab
$stmt = $conn->prepare("SELECT eventTitel, ablaufdatum FROM events WHERE eventId = ?");
$stmt->bind_param("s", $eventId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $event = $result->fetch_assoc();
    $jetztTimestamp = time(); // Aktueller Unix-Timestamp

    // Prüfe, ob das Event abgelaufen ist
    if ($jetztTimestamp > $event['ablaufdatum']) {
        echo json_encode(['status' => 'error', 'message' => 'Dieses Event ist bereits abgelaufen.']);
    } else {
        // Alles OK: Sende den offiziellen Event-Titel zurück
        echo json_encode(['status' => 'success', 'eventTitel' => $event['eventTitel']]);
    }
} else {
    // Event-ID wurde nicht in der Datenbank gefunden
    echo json_encode(['status' => 'error', 'message' => 'Ungültige Event-ID.']);
}

$stmt->close();
$conn->close();
?>