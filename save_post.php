<?php

require 'db_config.php';

header('Content-Type: application/json');

// --- Validierung ---
if (!isset($_POST['eventId'], $_POST['eventTitel'], $_POST['autor'], $_POST['text'])) {
    echo json_encode(['status' => 'error', 'message' => 'Fehlende Text-Daten.']);
    exit;
}

if (!isset($_FILES['fotos'])) {
    echo json_encode(['status' => 'error', 'message' => 'Keine Fotos hochgeladen.']);
    exit;
}

$fotoCount = count($_FILES['fotos']['name']);
if ($fotoCount > 4) {
    echo json_encode(['status' => 'error', 'message' => 'Maximal 4 Fotos erlaubt.']);
    exit;
}

// --- Datenbankverbindung ---
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Datenbankverbindung fehlgeschlagen.']);
    exit;
}

// --- 1. News-Beitrag speichern (ohne Fotos) ---
$conn->begin_transaction(); // Start einer Transaktion

try {
    $stmt = $conn->prepare("INSERT INTO event_news (eventId, eventTitel, autor, email, telefon, text) VALUES (?, ?, ?, ?, ?, ?)");
    $email = $_POST['email'] ?? null;
    $telefon = $_POST['telefon'] ?? null;
    $stmt->bind_param("ssssss", $_POST['eventId'], $_POST['eventTitel'], $_POST['autor'], $email, $telefon, $_POST['text']);
    
    if (!$stmt->execute()) {
        throw new Exception('Fehler beim Speichern des Beitrags: ' . $stmt->error);
    }
    
    $newsId = $conn->insert_id; // Die ID des soeben erstellten Beitrags holen
    $stmt->close();

    // --- 2. Jedes Foto in der neuen Tabelle speichern ---
    $stmtFotos = $conn->prepare("INSERT INTO event_news_fotos (newsId, foto) VALUES (?, ?)");

    for ($i = 0; $i < $fotoCount; $i++) {
        // Prüfen, ob bei diesem spezifischen Upload ein Fehler aufgetreten ist
        if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) {
            throw new Exception('Fehler beim Upload von Foto #' . ($i + 1));
        }

        $fotoBlob = file_get_contents($_FILES['fotos']['tmp_name'][$i]);
        
        $null = NULL;
        $stmtFotos->bind_param("ib", $newsId, $null);
        $stmtFotos->send_long_data(1, $fotoBlob); // BLOB an den zweiten Parameter binden

        if (!$stmtFotos->execute()) {
            throw new Exception('Fehler beim Speichern von Foto #' . ($i + 1) . ': ' . $stmtFotos->error);
        }
    }
    
    $stmtFotos->close();
    $conn->commit(); // Alle Änderungen bestätigen

    echo json_encode(['status' => 'success', 'message' => 'Beitrag mit ' . $fotoCount . ' Foto(s) erfolgreich gespeichert.']);

} catch (Exception $e) {
    $conn->rollback(); // Alle Änderungen rückgängig machen bei einem Fehler
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>