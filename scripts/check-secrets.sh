#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# check-secrets.sh
# Scans staged (or all tracked) files for secrets and sensitive values before
# committing / pushing to GitHub.
#
# Usage:
#   ./scripts/check-secrets.sh          # scan staged files (pre-commit mode)
#   ./scripts/check-secrets.sh --all    # scan entire working tree
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
RESET='\033[0m'

FOUND=0
MODE="${1:-}"

# ── Files to scan ─────────────────────────────────────────────────────────────
if [[ "$MODE" == "--all" ]]; then
    FILES=$(git ls-files)
else
    # Only staged files
    FILES=$(git diff --cached --name-only --diff-filter=ACMR)
fi

if [[ -z "$FILES" ]]; then
    echo -e "${GREEN}✔ No files to scan.${RESET}"
    exit 0
fi

# ── Blocked filenames ─────────────────────────────────────────────────────────
BLOCKED_FILES=(
    ".env"
    ".env.production"
    ".env.backup"
    ".env.local"
    "auth.json"
    "storage/*.key"
)

for pattern in "${BLOCKED_FILES[@]}"; do
    while IFS= read -r file; do
        # shellcheck disable=SC2053
        if [[ "$file" == $pattern ]]; then
            echo -e "${RED}✘ BLOCKED FILE staged: $file${RESET}"
            FOUND=1
        fi
    done <<< "$FILES"
done

# ── Secret patterns to grep for ───────────────────────────────────────────────
# Format: "description|regex"
PATTERNS=(
    "Cloudinary URL with credentials|cloudinary://[^@]+:[^@]+@"
    "AWS Access Key ID|AKIA[0-9A-Z]{16}"
    "AWS Secret Key (assignment)|AWS_SECRET_ACCESS_KEY\s*=\s*['\"]?[A-Za-z0-9/+=]{20,}"
    "Generic API key assignment|(?i)(api_key|api_secret|secret_key|access_token|auth_token|private_key)\s*=\s*['\"]?[A-Za-z0-9_\-]{16,}"
    "Xendit API key|xnd_(development|production)_[A-Za-z0-9]+"
    "Private key block|-----BEGIN (RSA |EC )?PRIVATE KEY-----"
    "Password assignment|(?i)password\s*=\s*['\"]?.{6,}"
    "APP_KEY with value|APP_KEY\s*=\s*base64:"
    "Database URL with password|mysql://[^:]+:[^@]+@"
    "Hardcoded Bearer token|Bearer\s+[A-Za-z0-9\-_]{20,}"
)

while IFS= read -r file; do
    # Skip binary files and non-existent files
    [[ -f "$file" ]] || continue
    file "$file" | grep -qiE 'text|json|script' || continue

    for entry in "${PATTERNS[@]}"; do
        desc="${entry%%|*}"
        pattern="${entry##*|}"

        if git diff --cached -- "$file" 2>/dev/null | grep -qP "$pattern" 2>/dev/null ||
           { [[ "$MODE" == "--all" ]] && grep -qP "$pattern" "$file" 2>/dev/null; }; then
            echo -e "${RED}✘ SECRET FOUND${RESET} — ${YELLOW}${desc}${RESET}"
            echo -e "  File: ${file}"
            FOUND=1
        fi
    done
done <<< "$FILES"

# ── .env accidentally re-added check ─────────────────────────────────────────
if git diff --cached --name-only | grep -qE '^\.env$'; then
    echo -e "${RED}✘ .env is staged! Remove it: git reset HEAD .env${RESET}"
    FOUND=1
fi

# ── Result ────────────────────────────────────────────────────────────────────
echo ""
if [[ $FOUND -ne 0 ]]; then
    echo -e "${RED}╔══════════════════════════════════════════════════╗${RESET}"
    echo -e "${RED}║  COMMIT BLOCKED — secrets or sensitive files     ║${RESET}"
    echo -e "${RED}║  detected. Fix the issues above before pushing.  ║${RESET}"
    echo -e "${RED}╚══════════════════════════════════════════════════╝${RESET}"
    exit 1
else
    echo -e "${GREEN}✔ No secrets detected. Safe to commit/push.${RESET}"
    exit 0
fi
