#!/usr/bin/env bash
# Build the module package in the Dolibarr distribution format:
#   build/module_prune-<version>.zip, with the module files under <module>/ (accepted by admin/modules.php)
# Usage: build/makepack.sh   (from anywhere, needs bash, rsync and zip)
set -euo pipefail

MODULE=prune
DESCRIPTOR=core/modules/modPrune.class.php

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

VERSION="$(sed -nE "s/^[[:space:]]*\\\$this->version[[:space:]]*=[[:space:]]*'([0-9][^']*)'.*/\1/p" "$DESCRIPTOR" | head -n 1)"
if [ -z "$VERSION" ]; then
	echo "Cannot find a numeric version in $DESCRIPTOR" >&2
	exit 1
fi

# admin/modules.php takes the module name from the file name, and only accepts digits and dots in the version
ZIPVERSION="${VERSION//-/.}"
ZIPFILE="$ROOT/build/module_${MODULE}-${ZIPVERSION}.zip"
STAGING="$(mktemp -d)"
trap 'rm -rf "$STAGING"' EXIT

# Development files are left out of the package (runtime dependencies in vendor/ are kept)
rsync -a \
	--exclude='/.git' \
	--exclude='/.github' \
	--exclude='/.gitignore' \
	--exclude='/.claude' \
	--exclude='/.superpowers' \
	--exclude='/.tx' \
	--exclude='/build' \
	--exclude='/codesniffer' \
	--exclude='/node_modules' \
	./ "$STAGING/$MODULE/"

rm -f "$ZIPFILE"
(cd "$STAGING" && zip -qr "$ZIPFILE" "$MODULE")

echo "Package built: build/$(basename "$ZIPFILE")"
