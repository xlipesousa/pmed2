#!/usr/bin/env bash
set -euo pipefail

ENV_FILE="${1:-.env}"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Arquivo não encontrado: $ENV_FILE" >&2
  exit 1
fi

python3 - "$ENV_FILE" <<'PY'
import re
import sys
from pathlib import Path

path = Path(sys.argv[1])
lines = path.read_text(encoding='utf-8').splitlines()

pattern = re.compile(r'^([A-Za-z_][A-Za-z0-9_]*)=(.*)$')
password_key = re.compile(r'.*_PASSWORD$')

out = []
for line in lines:
    m = pattern.match(line)
    if not m:
        out.append(line)
        continue

    key, value = m.group(1), m.group(2)
    if not password_key.match(key):
        out.append(line)
        continue

    value = value.strip()
    if (value.startswith("'") and value.endswith("'")) or (value.startswith('"') and value.endswith('"')):
        out.append(f"{key}={value}")
        continue

    escaped = value.replace("'", "'\"'\"'")
    out.append(f"{key}='{escaped}'")

path.write_text("\n".join(out) + "\n", encoding='utf-8')
print(f"Normalização aplicada em {path}")
PY
