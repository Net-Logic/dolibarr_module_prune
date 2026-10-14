---
title: "Prune - shared libraries for the Net-Logic modules"
short: "PHP libraries, encrypted OAuth token storage and cache shared by the Net-Logic modules (Microsoft 365, Google, Docusign)."
start: "Open **Home → Setup → Modules → Prune** to see the bundled libraries and choose the cache."
---
**Prune** is the shared library module of the Net-Logic modules: install it once and the modules that need it (Microsoft 365, Google, Docusign and others) find their PHP libraries, token storage and cache ready.

## Features

- **Bundled PHP libraries**: Microsoft Graph, Google API client, Docusign eSign, OVH, OAuth2 providers and an ICS parser, with the versions the Net-Logic modules are tested with.
- **Encrypted OAuth tokens**: access and refresh tokens are stored in the database, encrypted with Dolibarr's own encryption.
- **Cache layer**: file system or Memcached, chosen in the setup page, with a button to clear the cache.
- **Library list**: the setup page lists every bundled library with its version, namespace and home page.
- **Developer mode**: an option for troubleshooting the modules that rely on Prune.
