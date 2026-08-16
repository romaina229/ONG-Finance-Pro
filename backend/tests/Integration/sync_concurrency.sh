#!/usr/bin/env bash
set -euo pipefail

: "${PGHOST:=localhost}"
: "${PGPORT:=5432}"
: "${PGUSER:=postgres}"
: "${PGDATABASE:=finance_pro_test}"
: "${PGPASSWORD:=postgres}"
export PGHOST PGPORT PGUSER PGDATABASE PGPASSWORD

psql -v ON_ERROR_STOP=1 <<'SQL'
DROP TABLE IF EXISTS sync_concurrency_test;
CREATE TABLE sync_concurrency_test (
  id integer PRIMARY KEY,
  server_version bigint NOT NULL
);
INSERT INTO sync_concurrency_test(id, server_version) VALUES (1, 12);
SQL

# Transaction A locks the entity, advances the version, then holds the lock.
psql -v ON_ERROR_STOP=1 -c "BEGIN; SELECT server_version FROM sync_concurrency_test WHERE id=1 FOR UPDATE; UPDATE sync_concurrency_test SET server_version=13 WHERE id=1; SELECT pg_sleep(2); COMMIT;" >/tmp/sync-a.log 2>&1 &
A_PID=$!
sleep 0.4

# Transaction B must wait for A, then observe version 13. It must not silently
# write from stale base version 12.
RESULT=$(psql -At -v ON_ERROR_STOP=1 <<'SQL'
BEGIN;
SELECT server_version FROM sync_concurrency_test WHERE id=1 FOR UPDATE;
COMMIT;
SQL
)
wait "$A_PID"

if [[ "$RESULT" != "13" ]]; then
  echo "Concurrency test failed: expected locked read to observe version 13, got '$RESULT'"
  cat /tmp/sync-a.log
  exit 1
fi

echo "PASS: concurrent transaction observed the advanced server version after row lock."
