---
title: "Prune - librairies partagées des modules Net-Logic"
short: "Librairies PHP, stockage chiffré des jetons OAuth et cache partagés par les modules Net-Logic (Microsoft 365, Google...)."
start: "Ouvrez **Accueil → Configuration → Modules → Prune** pour voir les librairies fournies et choisir le cache."
---
**Prune** est le module de librairies partagées des modules Net-Logic : installé une fois, il fournit aux modules qui en ont besoin (Microsoft 365, Google, Docusign et d'autres) leurs librairies PHP, le stockage des jetons et le cache.

## Fonctionnalités

- **Librairies PHP fournies** : Microsoft Graph, client Google API, Docusign eSign, OVH, fournisseurs OAuth2 et un lecteur ICS, dans les versions avec lesquelles les modules Net-Logic sont testés.
- **Jetons OAuth chiffrés** : les jetons d'accès et de rafraîchissement sont stockés en base, chiffrés avec le chiffrement de Dolibarr.
- **Couche de cache** : système de fichiers ou Memcached, au choix dans la page de configuration, avec un bouton pour vider le cache.
- **Liste des librairies** : la page de configuration liste chaque librairie fournie avec sa version, son espace de noms et sa page d'accueil.
- **Mode développeur** : une option pour le dépannage des modules qui s'appuient sur Prune.
