// server.js

// 1. Notwendige Pakete importieren
const express = require('express');
const mysql = require('mysql2');
const cors = require('cors'); // Paket für die CORS-Freigabe

const app = express();
const port = 3000; // Der Port, auf dem der Server läuft (kann angepasst werden)

// 2. Middleware verwenden
// CORS-Freigabe: Erlaubt dem Frontend (andere Domain/anderer Port) den Zugriff
app.use(cors()); 
// JSON-Parser: Versteht die vom Frontend gesendeten JSON-Daten
app.use(express.json({ limit: '50mb' })); // Limit für Base64-Fotos erhöhen

// 3. Deine persönlichen Datenbank-Zugangsdaten
// WICHTIG: Ändere dein Passwort, falls noch не geschehen!
const dbConnection = mysql.createPool({
    host: 'database-5018680472.webspace-host.com',      // Den Hostnamen bekommst du von deinem Anbieter
    user: 'dbu1596664',
    password: 'Focke2212#', // <-- Trage hier dein NEUES, sicheres Passwort ein!
    database: 'dbs14794189'
}).promise();

// 4. API-Endpunkt, der die Daten vom Frontend empfängt
app.post('/save-post', async (req, res) => {
    // Daten aus dem Frontend-Request auslesen
    const { eventId, autor, email, telefon, text, fotoBase64 } = req.body;

    if (!eventId || !autor || !text) {
        return res.status(400).json({ status: 'error', message: 'Fehlende Daten.' });
    }

    // 5. Daten sicher in die Datenbank einfügen
    const sql = `
        INSERT INTO event_news 
        (eventId, autor, email, telefon, text, fotoBase64) 
        VALUES (?, ?, ?, ?, ?, ?)
    `;

    try {
        await dbConnection.query(sql, [eventId, autor, email, telefon, text, fotoBase64]);
        console.log('Beitrag erfolgreich für Event gespeichert:', eventId);
        res.json({ status: 'success', message: 'Beitrag erfolgreich gespeichert.' });
    } catch (error) {
        console.error("Fehler beim Speichern in der DB:", error);
        res.status(500).json({ status: 'error', message: 'Fehler beim Speichern in der Datenbank.' });
    }
});

// 6. Server starten und auf Anfragen warten
app.listen(port, () => {
    console.log(`Server läuft auf Port ${port} und wartet auf Anfragen...`);
});