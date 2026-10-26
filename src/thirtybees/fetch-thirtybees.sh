#!/usr/bin/env bash
#
# Fetch the pinned ThirtyBees source tree used by the Docker image.
#
# The source is not tracked in git. It is acquired here, verified against the
# immutable upstream commit, and placed in the ignored www/thirtybees directory.
#
# Usage:
#   ./fetch-thirtybees.sh [--if-missing] [destination]
#
# --if-missing  reuse an existing tree fetched from the same commit
# Default destination: <this script's directory>/www/thirtybees
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MODE="always"
if [ "${1:-}" = "--if-missing" ]; then
  MODE="if-missing"
  shift
fi
DEST="${1:-$SCRIPT_DIR/www/thirtybees}"
LOCK="$SCRIPT_DIR/thirtybees-submodules.lock"
OVERLAY="$SCRIPT_DIR/overlay"

UPSTREAM_REPO="https://github.com/thirtybees/thirtybees.git"
UPSTREAM_TAG="1.5.1"
UPSTREAM_COMMIT="e456bb60c0b1f7bf2a29734d5f4fc073e4fe4d47"

die() {
  printf 'fetch-thirtybees: %s\n' "$*" >&2
  exit 1
}

command -v git >/dev/null 2>&1 || die "git is required"
[ -f "$LOCK" ] || die "missing $LOCK"

if [ "$MODE" = "if-missing" ] && [ -f "$DEST/.thirtybees-commit" ] && \
   [ "$(cat "$DEST/.thirtybees-commit")" = "$UPSTREAM_COMMIT" ] && \
   [ -f "$DEST/install-dev/index_cli.php" ]; then
  printf 'ThirtyBees %s already present at %s (use make refresh-deps to refetch)\n' \
    "$UPSTREAM_TAG" "$DEST"
  exit 0
fi

TMP="$(mktemp -d "${TMPDIR:-/tmp}/thirtybees.XXXXXX")"
cleanup() {
  rm -rf "$TMP"
}
trap cleanup EXIT

printf 'Fetching ThirtyBees %s (%s)\n' "$UPSTREAM_TAG" "$UPSTREAM_COMMIT"
mkdir -p "$TMP/src"
git -C "$TMP/src" init -q
git -C "$TMP/src" remote add origin "$UPSTREAM_REPO"
git -C "$TMP/src" fetch -q --depth 1 origin "$UPSTREAM_COMMIT"
git -C "$TMP/src" checkout -q --detach FETCH_HEAD

ACTUAL="$(git -C "$TMP/src" rev-parse HEAD)"
[ "$ACTUAL" = "$UPSTREAM_COMMIT" ] ||
  die "upstream commit mismatch: expected $UPSTREAM_COMMIT, got $ACTUAL"

printf 'Fetching %s submodules at their recorded commits\n' "$(($(wc -l < "$LOCK") - 1))"
git -C "$TMP/src" submodule update --init --recursive --quiet

# Verify every submodule against the reviewed lock file, not just whatever
# the upstream .gitmodules currently points at.
while IFS=$'\t' read -r path commit url; do
  case "$path" in
    ''|'#'*) continue ;;
  esac
  [ -d "$TMP/src/$path" ] || die "missing submodule directory: $path"
  actual="$(git -C "$TMP/src/$path" rev-parse HEAD 2>/dev/null)" ||
    die "cannot read submodule revision: $path"
  [ "$actual" = "$commit" ] ||
    die "submodule $path is at $actual, expected $commit ($url)"
done < "$LOCK"

if git -C "$TMP/src" submodule status --recursive | grep -q '^-'; then
  die "one or more submodules were not initialized"
fi

# The build only needs the files; drop all VCS metadata.
find "$TMP/src" -type d -name .git -prune -exec rm -rf {} +
find "$TMP/src" -type f -name .git -delete

# Project-owned build overrides (PHP 8.3 composer manifest and lock).
[ -d "$OVERLAY" ] || die "missing overlay directory: $OVERLAY"
cp -R "$OVERLAY/." "$TMP/src/"

[ -f "$TMP/src/config/defines.inc.php" ] || die "incomplete ThirtyBees tree"
[ -f "$TMP/src/install-dev/index_cli.php" ] || die "installer is missing"
[ -f "$TMP/src/composer/php8.3/composer.lock" ] || die "composer lock is missing"

rm -rf "$DEST"
mkdir -p "$(dirname "$DEST")"
cp -a "$TMP/src/." "$DEST/"
printf '%s\n' "$UPSTREAM_COMMIT" > "$DEST/.thirtybees-commit"

printf 'ThirtyBees ready at %s\n' "$DEST"
printf 'Next: docker build (or make php) installs composer dependencies from the lock file.\n'
