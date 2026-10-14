---
title: "Prune - gemeinsame Bibliotheken der Net-Logic-Module"
short: "PHP-Bibliotheken, verschlüsselte OAuth-Token und Cache für die Net-Logic-Module (Microsoft 365, Google, Docusign...)."
start: "Öffnen Sie **Start → Einstellungen → Module → Prune**, um die Bibliotheken zu sehen und den Cache zu wählen."
---
**Prune** ist das gemeinsame Bibliotheksmodul der Net-Logic-Module: einmal installiert, finden die Module, die es benötigen (Microsoft 365, Google, Docusign und andere), ihre PHP-Bibliotheken, den Token-Speicher und den Cache bereit.

## Funktionen

- **Mitgelieferte PHP-Bibliotheken**: Microsoft Graph, Google-API-Client, Docusign eSign, OVH, OAuth2-Anbieter und ein ICS-Parser, in den Versionen, mit denen die Net-Logic-Module getestet sind.
- **Verschlüsselte OAuth-Token**: Zugriffs- und Aktualisierungstoken werden in der Datenbank gespeichert und mit der Verschlüsselung von Dolibarr geschützt.
- **Cache-Schicht**: Dateisystem oder Memcached, wählbar auf der Einstellungsseite, mit einer Schaltfläche zum Leeren des Caches.
- **Bibliotheksliste**: Die Einstellungsseite listet jede mitgelieferte Bibliothek mit Version, Namensraum und Homepage.
- **Entwicklermodus**: eine Option zur Fehlersuche in den Modulen, die Prune verwenden.
