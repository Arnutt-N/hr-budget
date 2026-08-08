#!/usr/bin/env bash
#
# Rebuild the PHPUnit test database from the development database schema.
#
# Why this exists:
#   database/hr_budget_only.sql was dumped with `mysqldump --databases`, so it
#   carries `CREATE DATABASE hr_budget` + `USE hr_budget` inside the file. Those
#   statements override whatever database you name on the command line, meaning
#   `mysql hr_budget_test < database/hr_budget_only.sql` silently rewrites the
#   REAL development database instead. This script dumps without --databases so
#   the target stays whatever we point it at.
#
# Usage:
#   ./scripts/setup_test_db.sh                 # hr_budget -> hr_budget_test
#   SOURCE_DB=foo TARGET_DB=foo_test ./scripts/setup_test_db.sh
#
set -euo pipefail

SOURCE_DB="${SOURCE_DB:-hr_budget}"
TARGET_DB="${TARGET_DB:-hr_budget_test}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_HOST="${DB_HOST:-127.0.0.1}"

# Guard: never let this script overwrite a non-test database.
case "$TARGET_DB" in
    *_test) ;;
    *)
        echo "REFUSING: TARGET_DB='$TARGET_DB' does not end in '_test'." >&2
        echo "This script DROPs the target database; it only targets test databases." >&2
        exit 1
        ;;
esac

if [ "$SOURCE_DB" = "$TARGET_DB" ]; then
    echo "REFUSING: SOURCE_DB and TARGET_DB are both '$SOURCE_DB'." >&2
    exit 1
fi

# Locate the MySQL client: PATH first, then the Laragon bundle.
find_bin() {
    local name="$1"
    if command -v "$name" >/dev/null 2>&1; then
        command -v "$name"
        return 0
    fi
    local candidate
    candidate=$(ls -d /d/laragon/bin/mysql/*/bin/"$name".exe 2>/dev/null | head -1 || true)
    if [ -n "$candidate" ]; then
        echo "$candidate"
        return 0
    fi
    echo "Could not find '$name'. Add it to PATH or start Laragon." >&2
    return 1
}

MYSQL=$(find_bin mysql)
MYSQLDUMP=$(find_bin mysqldump)

auth_args=(-h "$DB_HOST" -u "$DB_USER")
[ -n "$DB_PASS" ] && auth_args+=("-p$DB_PASS")

echo "Source : $SOURCE_DB"
echo "Target : $TARGET_DB"

# Verify the source actually has tables, so we fail loudly instead of
# producing an empty test database that makes every test error out.
table_count=$("$MYSQL" "${auth_args[@]}" -N -B -e \
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$SOURCE_DB';")
if [ "$table_count" -eq 0 ]; then
    echo "REFUSING: source database '$SOURCE_DB' has no tables." >&2
    exit 1
fi
echo "Found $table_count tables in $SOURCE_DB."

echo "Recreating $TARGET_DB ..."
"$MYSQL" "${auth_args[@]}" -e \
    "DROP DATABASE IF EXISTS \`$TARGET_DB\`;
     CREATE DATABASE \`$TARGET_DB\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# No --databases flag => no CREATE DATABASE / USE statements in the output,
# so the dump lands wherever we direct it.
echo "Copying schema + data ..."
"$MYSQLDUMP" "${auth_args[@]}" \
    --single-transaction \
    --routines \
    --default-character-set=utf8mb4 \
    "$SOURCE_DB" \
    | "$MYSQL" "${auth_args[@]}" --default-character-set=utf8mb4 "$TARGET_DB"

final_count=$("$MYSQL" "${auth_args[@]}" -N -B -e \
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$TARGET_DB';")
echo "Done. $TARGET_DB now has $final_count tables."
