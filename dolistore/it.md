---
title: "Prune - librerie condivise dei moduli Net-Logic"
short: "Librerie PHP, archiviazione cifrata dei token OAuth e cache dei moduli Net-Logic (Microsoft 365, Google, Docusign...)."
start: "Aprite **Home → Impostazioni → Moduli → Prune** per vedere le librerie incluse e scegliere la cache."
---
**Prune** è il modulo di librerie condivise dei moduli Net-Logic: installato una volta, i moduli che ne hanno bisogno (Microsoft 365, Google, Docusign e altri) trovano pronte le loro librerie PHP, l'archiviazione dei token e la cache.

## Funzionalità

- **Librerie PHP incluse**: Microsoft Graph, client Google API, Docusign eSign, OVH, provider OAuth2 e un lettore ICS, nelle versioni con cui sono testati i moduli Net-Logic.
- **Token OAuth cifrati**: i token di accesso e di aggiornamento sono salvati nel database, cifrati con la cifratura di Dolibarr.
- **Livello di cache**: file system o Memcached, a scelta nella pagina di impostazione, con un pulsante per svuotare la cache.
- **Elenco delle librerie**: la pagina di impostazione elenca ogni libreria inclusa con la sua versione, il suo namespace e la sua home page.
- **Modalità sviluppatore**: un'opzione per la diagnosi dei moduli che si appoggiano a Prune.
