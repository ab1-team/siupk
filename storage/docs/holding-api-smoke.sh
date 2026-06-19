#!/bin/bash
# Smoke test untuk API Holding SIDBM.
# Jalankan di server holding setelah deploy, dengan License sudah di-seed.
#
# Prasyarat:
#   1. Tabel `licenses` sudah ada & ada 1 row dengan kecamatan target.
#   2. ApiSecret (token) dan web_kec / web_alternatif sudah diketahui.
#   3. APP_URL adalah base URL tenant (subsidiary).
#
# Contoh penggunaan:
#   BASE=https://app.sidbm.net \
#   TOKEN=DbRz5uVJsttoNDuSYbYPHjDMpkPxoDOx1P75dl4G \
#   TENANT=app.sidbm.net \
#   bash storage/docs/holding-api-smoke.sh

set -euo pipefail

BASE="${BASE:?Set BASE=https://your-tenant}"
TOKEN="${TOKEN:?Set TOKEN=api_secret}"
TENANT="${TENANT:?Set TENANT=web_kec|web_alternatif}"

H1=(-H "X-Holding-Token: $TOKEN" -H "X-Holding-Tenant: $TENANT" -H "Accept: application/json")

run() {
  local name="$1"; shift
  local url="$1"; shift
  echo "=== $name ==="
  echo "GET $url"
  curl -sS -w "\nHTTP %{http_code}\n" "${H1[@]}" "$url" | head -100
  echo
}

# 1. Tanpa header → 401
echo "=== AUTH NEGATIVE (no headers) ==="
curl -sS -w "\nHTTP %{http_code}\n" "$BASE/api/v1/holding/laporan/neraca?tahun=2025&bulan=6&hari=30"
echo

# 2. Token salah → 401
echo "=== AUTH NEGATIVE (bad token) ==="
curl -sS -w "\nHTTP %{http_code}\n" \
  -H "X-Holding-Token: BUKAN_TOKEN_ASLI" \
  -H "X-Holding-Tenant: $TENANT" \
  "$BASE/api/v1/holding/laporan/neraca?tahun=2025&bulan=6&hari=30"
echo

run "Neraca (bulanan)"   "$BASE/api/v1/holding/laporan/neraca?tahun=2025&bulan=6&hari=30"
run "Neraca (tahunan)"   "$BASE/api/v1/holding/laporan/neraca?tahun=2025"
run "Laba Rugi"          "$BASE/api/v1/holding/laporan/laba-rugi?tahun=2025&bulan=6&hari=30"
run "Arus Kas (bulanan)" "$BASE/api/v1/holding/laporan/arus-kas?tahun=2025&bulan=6&hari=30"
run "Arus Kas (sem 1)"   "$BASE/api/v1/holding/laporan/arus-kas?tahun=2025&semester=1"
run "Arus Kas (sem 2)"   "$BASE/api/v1/holding/laporan/arus-kas?tahun=2025&semester=2"
run "Arus Kas (tahunan)" "$BASE/api/v1/holding/laporan/arus-kas?tahun=2025"
run "Perubahan Ekuitas"  "$BASE/api/v1/holding/laporan/perubahan-ekuitas?tahun=2025&bulan=6&hari=30"
run "CALK"               "$BASE/api/v1/holding/laporan/calk?tahun=2025&bulan=6&hari=30"

echo "Selesai. Periksa tiap response: success=true, ringkasan terisi, data shape sesuai HOLDING-API.md."
