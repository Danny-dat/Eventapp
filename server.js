// server.js

// 1. Notwendige Pakete importieren
const express = require('express');
const mysql = require('mysql2');
const app = express();
const port = 3000; // Der Port, auf dem der Server läuft

// Middleware, um JSON-Daten zu verstehen und das Limit für Base64-Fotos zu erhöhen
app.use(express.json({ limit: '50mb' })); 

// 2. Deine persönlichen Datenbank-Zugangsdaten
// WICHTIG: Ändere dein Passwort, falls noch nicht geschehen!
const dbConnection = mysql.createPool({
    host: 'database-5018680472.webspace-host.com',      // Den Hostnamen bekommst du von deinem Anbieter
    user: 'dbu1596664',
    password: 'Focke2212#', // <-- Trage hier dein NEUES, sicheres Passwort ein!
    database: 'dbs14794189'
}).promise();

// 3. API-Endpunkt, der die Daten vom Frontend empfängt
app.post('/save-post', async (req, res) => {
    // Daten aus dem Frontend auslesen
    const { eventId, autor, email, telefon, text, fotoBase64 } = req.body;

    if (!eventId || !autor || !text) {
        return res.status(400).json({ status: 'error', message: 'Fehlende Daten.' });
    }

    // 4. Daten sicher in die Datenbank einfügen
    const sql = `
        INSERT INTO event_news 
        (eventId, autor, email, telefon, text, fotoBase64) 
        VALUES (?, ?, ?, ?, ?, ?)
    `;

    try {
        await dbConnection.query(sql, [eventId, autor, email, telefon, text, fotoBase64]);
        res.json({ status: 'success', message: 'Beitrag erfolgreich gespeichert.' });
    } catch (error) {
        console.error("Fehler beim Speichern in der DB:", error);
        res.status(500).json({ status: 'error', message: 'Fehler beim Speichern in der Datenbank.' });
    }
});

// 5. Server starten und auf Anfragen warten
app.listen(port, () => {
    console.log(`Server läuft auf Port ${port}`);
});