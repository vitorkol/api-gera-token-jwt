#!/usr/bin/env bash
# Smoke test E2E da API JWT — Q.A (evidência em codeReview/005-qa-report.md)
# Uso: API no ar em http://localhost:8000, depois: bash scripts/smoke-test.sh
set -u
BASE="http://localhost:8000/api"
pass=0; fail=0

check() { # check <descricao> <esperado> <obtido>
  if [ "$2" = "$3" ]; then
    pass=$((pass+1)); echo "PASS  $1 (HTTP $3)"
  else
    fail=$((fail+1)); echo "FAIL  $1 (esperado $2, obtido $3)"
  fi
}

code() { curl -s -o /dev/null -w "%{http_code}" "$@"; }

tok() { echo "$1" | sed -n 's/.*"access_token":"\([^"]*\)".*/\1/p'; }

echo "=== Smoke test E2E — API JWT (Laravel + Docker) ==="

# 1. Logins válidos
ADMIN=$(tok "$(curl -s -X POST $BASE/login -H 'Content-Type: application/json' -H 'Accept: application/json' -d '{"email":"admin@example.com","password":"password"}')")
USER=$(tok "$(curl -s -X POST $BASE/login -H 'Content-Type: application/json' -H 'Accept: application/json' -d '{"email":"user@example.com","password":"password"}')")
[ -n "$ADMIN" ] && { pass=$((pass+1)); echo "PASS  login admin gera token (${#ADMIN} chars)"; } || { fail=$((fail+1)); echo "FAIL  login admin"; }
[ -n "$USER" ]  && { pass=$((pass+1)); echo "PASS  login user gera token (${#USER} chars)"; }  || { fail=$((fail+1)); echo "FAIL  login user"; }

# 2. Credenciais inválidas
check "login senha errada => 401" 401 "$(code -X POST $BASE/login -H 'Content-Type: application/json' -H 'Accept: application/json' -d '{"email":"admin@example.com","password":"errada"}')"

# 3. GET /api/role — valida token e devolve a rule
check "role sem token => 401" 401 "$(code $BASE/role -H 'Accept: application/json')"
check "role admin => 200" 200 "$(code $BASE/role -H 'Accept: application/json' -H "Authorization: Bearer $ADMIN")"
check "role user => 200" 200 "$(code $BASE/role -H 'Accept: application/json' -H "Authorization: Bearer $USER")"
R=$(curl -s $BASE/role -H 'Accept: application/json' -H "Authorization: Bearer $USER")
[ "$R" = '{"role":"user"}' ] && { pass=$((pass+1)); echo 'PASS  payload role user = {"role":"user"}'; } || { fail=$((fail+1)); echo "FAIL  payload role user: $R"; }

# 4. GET /api/me
check "me com token => 200" 200 "$(code $BASE/me -H 'Accept: application/json' -H "Authorization: Bearer $ADMIN")"
check "me sem token => 401" 401 "$(code $BASE/me -H 'Accept: application/json')"

# 5. Validação de rule — /api/admin/ping
check "admin/ping admin => 200" 200 "$(code $BASE/admin/ping -H 'Accept: application/json' -H "Authorization: Bearer $ADMIN")"
check "admin/ping user => 403" 403 "$(code $BASE/admin/ping -H 'Accept: application/json' -H "Authorization: Bearer $USER")"
check "admin/ping sem token => 401" 401 "$(code $BASE/admin/ping -H 'Accept: application/json')"

# 6. Token adulterado (assinatura inválida)
check "token adulterado => 401" 401 "$(code $BASE/me -H 'Accept: application/json' -H "Authorization: Bearer ${ADMIN%?}x")"

# 7. Refresh
NEW=$(tok "$(curl -s -X POST $BASE/refresh -H 'Accept: application/json' -H "Authorization: Bearer $USER")")
[ -n "$NEW" ] && [ "$NEW" != "$USER" ] && { pass=$((pass+1)); echo "PASS  refresh gera token novo"; } || { fail=$((fail+1)); echo "FAIL  refresh"; }
check "me com token renovado => 200" 200 "$(code $BASE/me -H 'Accept: application/json' -H "Authorization: Bearer $NEW")"

# 8. Logout invalida o token (blacklist)
check "logout admin => 200" 200 "$(code -X POST $BASE/logout -H 'Accept: application/json' -H "Authorization: Bearer $ADMIN")"
check "me com token logoutado => 401" 401 "$(code $BASE/me -H 'Accept: application/json' -H "Authorization: Bearer $ADMIN")"

# 9. Register + role fixa user
EMAIL="qa-smoke-$(date +%s)@example.com"
check "register => 201" 201 "$(code -X POST $BASE/register -H 'Content-Type: application/json' -H 'Accept: application/json' -d "{\"name\":\"Qa Smoke\",\"email\":\"$EMAIL\",\"password\":\"senha-forte-123\",\"password_confirmation\":\"senha-forte-123\"}")"
QTOKEN=$(tok "$(curl -s -X POST $BASE/login -H 'Content-Type: application/json' -H 'Accept: application/json' -d "{\"email\":\"$EMAIL\",\"password\":\"senha-forte-123\"}")")
R=$(curl -s $BASE/role -H 'Accept: application/json' -H "Authorization: Bearer $QTOKEN")
[ "$R" = '{"role":"user"}' ] && { pass=$((pass+1)); echo "PASS  novo usuario tem role user"; } || { fail=$((fail+1)); echo "FAIL  role novo usuario: $R"; }

echo "=== Resultado: $pass PASS / $fail FAIL ==="
[ $fail -eq 0 ]
