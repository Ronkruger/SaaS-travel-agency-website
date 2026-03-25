#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# install-hooks.sh
# Installs check-secrets as both a pre-commit AND pre-push git hook.
# Run once after cloning:  bash scripts/install-hooks.sh
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
HOOK_DIR="$REPO_ROOT/.git/hooks"

chmod +x "$REPO_ROOT/scripts/check-secrets.sh"

# ── pre-commit: scans staged files before every commit ───────────────────────
PRE_COMMIT="$HOOK_DIR/pre-commit"
cat > "$PRE_COMMIT" <<'HOOK'
#!/usr/bin/env bash
# Auto-installed by scripts/install-hooks.sh
exec "$(git rev-parse --show-toplevel)/scripts/check-secrets.sh"
HOOK
chmod +x "$PRE_COMMIT"
echo "✔ pre-commit hook installed  → scans staged files on every 'git commit'"

# ── pre-push: scans entire working tree before every push ────────────────────
PRE_PUSH="$HOOK_DIR/pre-push"
cat > "$PRE_PUSH" <<'HOOK'
#!/usr/bin/env bash
# Auto-installed by scripts/install-hooks.sh
exec "$(git rev-parse --show-toplevel)/scripts/check-secrets.sh" --all
HOOK
chmod +x "$PRE_PUSH"
echo "✔ pre-push hook installed    → scans all tracked files on every 'git push'"
