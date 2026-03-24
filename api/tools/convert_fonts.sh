#!/usr/bin/env bash
set -euo pipefail

# Script de conversion TTF -> WOFF2 pour Hebasurf
# Usage: ./convert_fonts.sh
# Dépose les .ttf dans api/fonts/ puis lance le script depuis la racine du projet.

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
FONT_DIR="$ROOT_DIR/fonts"

TTFS=(
  "Righteous-Regular.ttf"
  "Inter-VariableFont_opsz,wght.ttf"
  "Inter-Italic-VariableFont_opsz,wght.ttf"
)

if [ ! -d "$FONT_DIR" ]; then
  echo "Dossier de polices introuvable : $FONT_DIR"
  exit 1
fi

need_convert=()
for f in "${TTFS[@]}"; do
  if [ -f "$FONT_DIR/$f" ]; then
    base="${f%.*}"
    if [ ! -f "$FONT_DIR/$base.woff2" ]; then
      need_convert+=("$f")
    else
      echo "OK: $f -> already has $base.woff2"
    fi
  else
    echo "MISSING: $FONT_DIR/$f"
  fi
done

if [ ${#need_convert[@]} -eq 0 ]; then
  echo "Aucune conversion nécessaire."
  exit 0
fi

echo "Fichiers à convertir: ${need_convert[*]}"

compress_with_woff2() {
  if command -v woff2_compress >/dev/null 2>&1; then
    for f in "$@"; do
      echo "Converting with woff2_compress: $f"
      (cd "$FONT_DIR" && woff2_compress "$f")
    done
    return 0
  fi
  return 1
}

compress_with_pyftsubset() {
  if command -v pyftsubset >/dev/null 2>&1; then
    for f in "$@"; do
      base="${f%.*}"
      echo "Converting with pyftsubset: $f -> $base.woff2"
      pyftsubset "$FONT_DIR/$f" --output-file="$FONT_DIR/$base.woff2" --flavor=woff2 --layout-features='*' --glyphs='*'
    done
    return 0
  fi
  return 1
}

if compress_with_woff2 "${need_convert[@]}"; then
  echo "Conversion terminée avec woff2_compress."
  exit 0
fi

if compress_with_pyftsubset "${need_convert[@]}"; then
  echo "Conversion terminée avec pyftsubset."
  exit 0
fi

echo "Aucun outil de conversion (woff2_compress ou pyftsubset) trouvé."
echo "Sur Debian/Ubuntu : sudo apt install woff2 fonttools" 
echo "Ou installe via pip : pip install fonttools" 
exit 2
