#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# install-hooks.sh
# Installs the check-secrets pre-commit hook into .git/hooks/.
# Run once after cloning:  bash scripts/install-hooks.sh
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
HOOK_DIR="$REPO_ROOT/.git/hooks"
HOOK_FILE="$HOOK_DIR/pre-commit"

cat > "$HOOK_FILE" <<'HOOK'
#!/usr/bin/env bash
# Auto-installed by scripts/install-hooks.sh
exec "$(git rev-parse --show-toplevel)/scripts/check-secrets.sh"
HOOK

chmod +x "$HOOK_FILE"
chmod +x "$REPO_ROOT/scripts/check-secrets.sh"

echo "✔ Pre-commit hook installed at $HOOK_FILE"
echo "  Every 'git commit' will now scan for secrets automatically."
