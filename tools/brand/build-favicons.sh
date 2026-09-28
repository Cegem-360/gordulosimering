#!/usr/bin/env bash
# Rasterises public/favicon.svg into public/favicon.ico and
# public/apple-touch-icon.png.
#
# public/favicon.svg is the source of truth: the logo's bearing "G"
# (resources/images/GS-logo.webp), redrawn as simple filled shapes on the
# logo's text blue. The logo is only 280x59 px, too small to trace the mark
# from, so it was drawn by hand after resources/images/product-placeholder.svg.
# Keep it to filled shapes: ImageMagick's built-in SVG renderer ignores strokes.
#
# Requires ImageMagick 7 (`brew install imagemagick`). Run from the repo root:
#   bash tools/brand/build-favicons.sh
set -euo pipefail

cd "$(dirname "$0")/../.."

SRC="public/favicon.svg"
[ -f "$SRC" ] || { echo "missing $SRC" >&2; exit 1; }

TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

# Render large once, then downsample: the SVG rasteriser is sharper from a big
# canvas than rendering small directly.
magick -background none -density 1536 "$SRC" -resize 512x512 "$TMP/base.png"

# Apple touch icon: 180x180, square and opaque. iOS rounds the corners itself
# and composites transparency badly, so render the tile without its radius.
sed 's/ rx="14"//' "$SRC" > "$TMP/square.svg"
magick -background none -density 1536 "$TMP/square.svg" -resize 180x180 \
  -background '#0F50A9' -alpha remove -alpha off public/apple-touch-icon.png

# Multi-resolution .ico for legacy browsers and Windows pinned sites.
for size in 16 32 48; do
  magick "$TMP/base.png" -resize "${size}x${size}" "$TMP/$size.png"
done
magick "$TMP/16.png" "$TMP/32.png" "$TMP/48.png" public/favicon.ico

echo "built:"
for f in public/favicon.svg public/favicon.ico public/apple-touch-icon.png; do
  printf '  %-32s %s bytes\n' "$f" "$(stat -f%z "$f" 2>/dev/null || stat -c%s "$f")"
done
