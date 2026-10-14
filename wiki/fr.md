# Présentation

**Prune** est le module de librairies partagées des modules Net-Logic : installé une fois, il fournit aux modules qui en ont besoin ([Doli 365](<wiki:Doli 365>), [Docusign](wiki:Docusign), Google et d'autres) leurs librairies PHP, le stockage des jetons et le cache.

Prune est gratuit et se télécharge sur DoliStore (lien dans l'encadré ci-dessus).

# Fonctionnalités

- **Librairies PHP fournies** : Microsoft Graph, client Google API, Docusign eSign, OVH, fournisseurs OAuth2 et un lecteur ICS, dans les versions avec lesquelles les modules Net-Logic sont testés.
- **Jetons OAuth chiffrés** : les jetons d'accès et de rafraîchissement sont stockés en base, chiffrés avec le chiffrement de Dolibarr.
- **Couche de cache** : système de fichiers ou Memcached, au choix dans la page de configuration, avec un bouton pour vider le cache.
- **Liste des librairies** : la page de configuration liste chaque librairie fournie avec sa version, son espace de noms et sa page d'accueil.
- **Mode développeur** : une option pour le dépannage des modules qui s'appuient sur Prune.

# Installation

1. Téléchargez le zip du module sur DoliStore.
2. Dans Dolibarr, ouvrez **Accueil → Configuration → Modules / Applications**, onglet **Déployer/Installer un module externe**, et envoyez le zip.
3. Dans la liste des modules, activez **Prune**.
4. Ouvrez **Accueil → Configuration → Modules → Prune** pour voir les librairies fournies et choisir le cache.

# Support

Pour toute question : [dolibarr@netlogic.fr](mailto:dolibarr@netlogic.fr).
