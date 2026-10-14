---
title: "Prune - librerías compartidas de los módulos Net-Logic"
short: "Las librerías PHP, el almacenamiento cifrado de tokens OAuth y la caché de los módulos Net-Logic (Microsoft 365, Google...)."
start: "Abra **Inicio → Configuración → Módulos → Prune** para ver las librerías incluidas y elegir la caché."
---
**Prune** es el módulo de librerías compartidas de los módulos Net-Logic: instalado una vez, los módulos que lo necesitan (Microsoft 365, Google, Docusign y otros) encuentran listas sus librerías PHP, el almacenamiento de tokens y la caché.

## Funcionalidades

- **Librerías PHP incluidas**: Microsoft Graph, cliente de Google API, Docusign eSign, OVH, proveedores OAuth2 y un lector ICS, en las versiones con las que se prueban los módulos Net-Logic.
- **Tokens OAuth cifrados**: los tokens de acceso y de actualización se guardan en la base de datos, cifrados con el cifrado de Dolibarr.
- **Capa de caché**: sistema de archivos o Memcached, a elegir en la página de configuración, con un botón para vaciar la caché.
- **Lista de librerías**: la página de configuración muestra cada librería incluida con su versión, su espacio de nombres y su página web.
- **Modo desarrollador**: una opción para diagnosticar los módulos que dependen de Prune.
