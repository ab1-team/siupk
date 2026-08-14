# Generate Simpanan, Tabel `kode_simp`, dan Klasifikasi Mutasi

Dokumentasi teknis untuk fitur **Generate Simpanan** (sinkronisasi `real_simpanan_<lokasi>` dari `transaksi_<lokasi>`) dan penggunaan tabel referensi `kode_simp` plus service `KodeMutasiClassifier`.

> Berlaku untuk codebase **siupk** setelah refactor `KodeMutasiClassifier` (Agustus 2026).

---

## 1. Tujuan

Mengisi ulang tabel **`real_simpanan_<lokasi>`** dari transaksi simpanan anggota di **`transaksi_<lokasi>`** agar saldo kumulatif (`sum`) konsisten dengan jurnal. Setiap generate akan:

1. Menghapus seluruh baris `real_simpanan_<lokasi>` milik CIF tertentu.
2. Membaca ulang transaksi CIF tsb (yang tidak di-soft-delete).
3. Mengklasifikasi jenis transaksi (`kode`) lewat service **`KodeMutasiClassifier`** (yang membaca tabel referensi `kode_simp` via `KodeSimp::resolveKode()`).
4. Menyimpan baris baru dengan saldo kumulatif (`sum`) yang diperbarui.
5. Menulis log audit per-CIF.

Semua proses dibungkus dalam `DB::transaction` agar gagal di tengah tidak meninggalkan state parsial.

---

## 2. Komponen

| Komponen | Lokasi | Peran |
|---|---|---|
| `KodeSimp` (model) | `app/Models/KodeSimp.php` | Tabel referensi nilai `kode`; konstanta id mutasi; algoritma `resolveKode()` per lokasi. |
| `JenisSimpanan` (model) | `app/Models/JenisSimpanan.php` | Tabel referensi produk simpanan; nomor rekening kas/simpanan/bunga/pajak/admin per produk (dipakai `KodeMutasiClassifier` untuk placeholder `jenis_simp.*`). |
| `KodeMutasiClassifier` (service) | `app/Services/KodeMutasiClassifier.php` | Daftar aturan klasifikasi mutasi (15 rules). Mencocokkan debit/kredit transaksi terhadap pola prefix rekening atau placeholder JenisSimpanan. |
| `RealSimpananGenerator` (service) | `app/Services/RealSimpananGenerator.php` | Logika terpusat delete-muat-klasifikasi-simpan + `DB::transaction` + logging. Delegasi klasifikasi ke `KodeMutasiClassifier`. |
| `GenerateBungaController` | `app/Http/Controllers/GenerateBungaController.php` | Endpoint batch banyak CIF (`GET /generate_simpanan`). |
| `SimpananController::generateSimpanan` | `app/Http/Controllers/SimpananController.php` | Endpoint batch via `DB::table` (`GET /simpanan/generate`). |
| `SimpananController::generateSimpanan2` | `app/Http/Controllers/SimpananController.php` | Endpoint single CIF via AJAX (`POST /simpanan/generate/{cif}`). |
| `RegenerateRealSimpanan` (command) | `app/Console/Commands/RegenerateRealSimpanan.php` | CLI untuk re-generate massal. |

---

## 3. Tabel `kode_simp`

### 3.1 Struktur

| Kolom | Tipe | Arti |
|---|---|---|
| `id` | int PK AI | Identifier internal baris (saat ini 1..15). |
| `kode` | int | **Nilai yang ditulis ke `real_simpanan_<lokasi>.kode`**. |
| `def` | int | Nilai default universal (saat ini sama dengan `kode`; dicadangkan untuk dokumentasi). |
| `nama_kode` | varchar(20) | Label/caption (untuk tampilan). |
| `lokasi` | int | `0` = untuk semua lokasi; selain `0` = override untuk lokasi tertentu. |
| `kecuali` | text | CSV id lokasi yang dikecualikan dari baris ini. |

### 3.2 Konstanta mutasi (di `KodeSimp`)

| Konstanta | Nilai `id` | Mutasi |
|---|---|---|
| `KodeSimp::MUTASI_SETOR_AWAL`      | 1  | Setoran awal (kas → simpanan, transaksi pertama per CIF) |
| `KodeSimp::MUTASI_SETOR`           | 2  | Setoran tunai (kas → simpanan, transaksi setelah setor awal) |
| `KodeSimp::MUTASI_TARIK`           | 3  | Penarikan tunai (simpanan → kas) |
| `KodeSimp::MUTASI_BUNGA`           | 4  | Bunga simpanan (`rek_bunga` → `rek_simp`) |
| `KodeSimp::MUTASI_ADMIN`           | 5  | Biaya admin (simpanan → `rek_adm`) |
| `KodeSimp::MUTASI_TRANSFER_MASUK`  | 6  | Transfer masuk (kas/bank → rekening piutang/simpanan) |
| `KodeSimp::MUTASI_TRANSFER_KELUAR` | 7  | Transfer keluar (piutang/simpanan → kas/bank) |
| `KodeSimp::MUTASI_KOREKSI_KREDIT`  | 8  | Koreksi kredit (rekening beban → simpanan) |
| `KodeSimp::MUTASI_KOREKSI_DEBET`   | 9  | Koreksi debet (simpanan → rekening beban) |
| `KodeSimp::MUTASI_PAJAK`           | 10 | Pajak simpanan (simpanan → `rek_pajak`) |
| `KodeSimp::MUTASI_SETOR_BERJANGKA` | 11 | Setoran berjangka (kas → rekening deposito) |
| `KodeSimp::MUTASI_PENCAIRAN_DEPOSITO` | 12 | Pencairan deposito (deposito → kas) |
| `KodeSimp::MUTASI_CETAK_REKENING_KORAN` | 13 | Biaya cetak rekening koran |
| `KodeSimp::MUTASI_TUTUP_REKENING`  | 14 | Tutup rekening (pengalihan saldo simpanan ke beban/modal) |
| `KodeSimp::MUTASI_PEMINDAHBUKUAN`  | 15 | Pemindahbukuan antar kas/bank |

Semua 15 id sudah dipetakan ke aturan klasifikasi di `KodeMutasiClassifier` (lihat §4).

### 3.3 Algoritma lookup `KodeSimp::resolveKode($idMutasi, $lokasiAktif)`

Urutan prioritas:

1. **Baris default universal** (`lokasi = 0`, `id = :idMutasi`) yang `kecuali` **tidak** mengandung `:lokasiAktif` → kembalikan `.kode`.
2. **Baris override lokasi** (`lokasi = :lokasiAktif`, `id = :idMutasi`) yang `kecuali` **tidak** mengandung `:lokasiAktif` → kembalikan `.kode`.
3. **Fallback**: baris `lokasi = 0` untuk `:idMutasi` (abaikan syarat `kecuali`) → kembalikan `.kode`.
4. **Tidak ada baris sama sekali** → kembalikan `null`.

Pemetaan dengan CSV: token dipisah koma, di-trim, dicocokkan eksak per integer.

#### Contoh data

| id | kode | def | nama_kode | lokasi | kecuali |
|---|---|---|---|---|---|
| 1 | 1 | 1 | Setoran Awal | 0 | `1,2` |
| 2 | 20 | 1 | Setoran Awal | 1 | NULL |
| 3 | 1 | 1 | Setoran Awal | 2 | NULL |

Lookup `Setoran Awal` per lokasi:

| lokasi_aktif | Hasil | Alasan |
|---|---|---|
| 1 | **20** | default (id=1) dikecualikan → override (id=2) dipakai. |
| 2 | **1** | default (id=1) dikecualikan → override (id=3) dipakai. |
| 3 | **1** | default (id=1) tidak dikecualikan → default dipakai. |

---

## 4. Klasifikasi mutasi via `KodeMutasiClassifier`

### 4.1 Sumber pola

`KodeMutasiClassifier` mencocokkan rekening debit/kredit transaksi terhadap dua jenis pola:

| Pola | Arti | Cocok bila |
|---|---|---|
| `jenis_simp.<kolom>` | Placeholder exact-match kolom JenisSimpanan | `trx.rekening_debit === jenisSimpanan.<kolom>` |
| `prefix%` atau `2.1.%` | Prefix generik (LIKE match) | `trx.rekening_debit` dimulai dengan `prefix` |
| `string` | String biasa (exact match) | `trx.rekening_debit === string` |

Placeholder `jenis_simp.*` yang dipakai: `rek_kas`, `rek_simp`, `rek_bunga`, `rek_pajak`, `rek_adm`.

### 4.2 Struktur rule

Setiap rule di `KodeMutasiClassifier::RULES` adalah array asosiatif:

| Field | Tipe | Arti |
|---|---|---|
| `id_mutasi` | int | Konstanta `KodeSimp` (1..15) |
| `label` | string | Deskripsi singkat (untuk log/debug) |
| `debet` | string[] | Array pola rekening debit (lihat §4.1) |
| `kredit` | string[] | Array pola rekening kredit (lihat §4.1) |
| `requires_setor_awal` | bool\|null | `true` = hanya baris pertama per CIF; `false` = baris setelahnya; `null` = abaikan |
| `direction` | `'masuk'`\|`'keluar'` | `'masuk'` → `real_k=jumlah, sum+=jumlah`; `'keluar'` → `real_d=jumlah, sum-=jumlah` |

Aturan dievaluasi berurutan sesuai urutan array; rule pertama yang cocok akan menang.

### 4.3 Daftar aturan (15 rules)

| id `kode_simp` | Mutasi | Pola debet | Pola kredit | `requires_setor_awal` | `direction` |
|---|---|---|---|---|---|
| 1 | Setoran Awal         | `jenis_simp.rek_kas`   | `jenis_simp.rek_simp`  | true  | masuk |
| 2 | Setoran Tunai        | `jenis_simp.rek_kas`   | `jenis_simp.rek_simp`  | false | masuk |
| 3 | Penarikan Tunai      | `jenis_simp.rek_simp`  | `jenis_simp.rek_kas`   | null  | keluar |
| 4 | Bunga Simpanan       | `jenis_simp.rek_bunga` | `jenis_simp.rek_simp`  | null  | masuk |
| 5 | Biaya Admin          | `jenis_simp.rek_simp`  | `jenis_simp.rek_adm`   | null  | keluar |
| 6 | Transfer Masuk       | `1.1.01%`, `1.1.02%`   | `2.1.%`, `3.1.%`       | null  | masuk |
| 7 | Transfer Keluar      | `2.1.%`, `3.1.%`       | `1.1.01%`, `1.1.02%`   | null  | keluar |
| 8 | Koreksi Kredit       | `4.1.%`, `5.1.%`, `5.2.%` | `2.1.%`, `3.1.%`   | null  | masuk |
| 9 | Koreksi Debet        | `2.1.%`, `3.1.%`       | `4.1.%`, `5.1.%`, `5.2.%` | null  | keluar |
| 10 | Pajak Simpanan      | `jenis_simp.rek_simp`  | `jenis_simp.rek_pajak` | null  | keluar |
| 11 | Setoran Berjangka   | `1.1.01%`              | `2.2.%`                | false | masuk |
| 12 | Pencairan Deposito  | `2.2.%`                | `1.1.01%`              | null  | keluar |
| 13 | Cetak Rekening Koran| `1.1.01%`              | `4.1.03%`              | null  | keluar |
| 14 | Tutup Rekening      | `2.1.%`, `2.2.%`       | `3.2.%`, `4.1.%`       | null  | keluar |
| 15 | Pemindahbukuan      | `1.1.01%`, `1.1.02%`, `1.1.03%` | `1.1.01%`, `1.1.02%`, `1.1.03%` | null  | keluar |

### 4.4 Alur klasifikasi

Untuk setiap transaksi, `KodeMutasiClassifier::classify()` berurutan:

1. **Siapkan nilai rekening** dari transaksi (`rekening_debit`, `rekening_kredit`).
2. **Iterasi `RULES`** dari index 0:
   - Jika `requires_setor_awal === true` dan flag `$str` bukan 1 → skip.
   - Jika `requires_setor_awal === false` dan flag `$str` sama dengan 1 → skip.
   - Cocokkan kolom debet dengan pola `debet` (placeholder atau prefix).
   - Cocokkan kolom kredit dengan pola `kredit`.
   - **Rule pertama yang cocok** → resolve `kode` via `KodeSimp::resolveKode()` dan hitung `real_d`/`real_k`/`sum` sesuai `direction`.
3. **Tidak ada rule yang cocok** → tulis `kode = 0` (transaksi tidak terklasifikasi, perlu dicek manual).

`$str` adalah flag estadoar awal per CIF: `1` = CIF baru (belum pernah setor), `2` = CIF sudah pernah setor. Flag di-reset ke `2` setelah rule `MUTASI_SETOR_AWAL` cocok.

### 4.5 Sumber row JenisSimpanan

`RealSimpananGenerator::generateForCif()` memuat baris `jenis_simpanan` **sekali per CIF** (sebelum loop klasifikasi) lewat:

```php
$simpananRow = DB::table($tableSimpanan)->where('id', $cif)->first();
$jenisSimpanan = JenisSimpanan::find($simpananRow->jenis_simpanan);
```

Baris `jenis_simpanan` dipakai oleh `KodeMutasiClassifier` untuk **expand placeholder** `jenis_simp.rek_kas`, `jenis_simp.rek_simp`, dll Baris ini **tidak boleh** ditebak dari reverse-lookup `rek_simp` transaksi karena `id=3` (Simpanan Program) dan `id=4` (Utang) memiliki `rek_simp` yang sama (`2.2.05.02`) — lihat §5.1.

---

## 5. Tabel `jenis_simpanan`

### 5.1 Sumber pola: kolom rekening

`KodeMutasiClassifier` placeholder `jenis_simp.*` membaca kolom berikut dari tabel `jenis_simpanan`:

| Kolom | Arti |
|---|---|
| `id` | Identifier produk simpanan. |
| `nama_js` | Nama produk (mis. "Simpanan Umum", "Simpanan Deposito"). |
| `rek_kas` | Nomor rekening kas lawan transaksi (setor/tarik tunai). |
| `rek_simp` | Nomor rekening simpanan itu sendiri. |
| `rek_bunga` | Nomor rekening beban bunga. |
| `rek_pajak` | Nomor rekening pajak simpanan. |
| `rek_adm` | Nomor rekening pendapatan biaya admin. |
| `file` | `1` = tanpa tgl tutup, `2` = pakai tgl tutup. |
| `lokasi`, `kode_kab`, `kecuali` | Kolom override per lokasi (struktur sama seperti `kode_simp`, lihat §3.3) — saat ini seluruh baris contoh masih `lokasi = 0`. |

Contoh data aktif:

| id | nama_js | rek_kas | rek_simp | rek_bunga | rek_pajak | rek_adm |
|---|---|---|---|---|---|---|
| 1 | Simpanan Umum | 1.1.01.09 | 2.1.05.01 | 5.2.01.01 | 2.1.03.01 | 4.1.03.01 |
| 2 | Simpanan Deposito | 1.1.01.09 | 2.2.05.01 | 5.2.01.01 | 2.1.03.01 | 4.1.03.02 |
| 3 | Simpanan Program | 1.1.01.09 | 2.2.05.02 | 5.2.01.01 | 2.1.03.01 | 4.1.03.03 |
| 4 | Utang | 1.1.01.12 | 2.2.05.02 | 5.2.01.01 | 2.1.03.01 | 4.1.03.03 |

> **Catatan**: id `3` (Simpanan Program) dan id `4` (Utang) memiliki `rek_simp` yang sama persis (`2.2.05.02`). Karena itu, `KodeMutasiClassifier` **tidak** menentukan baris `jenis_simpanan` dengan menebak dari `rek_simp` transaksi (reverse lookup) — hasilnya ambigu untuk dua produk ini. Baris `jenis_simpanan` yang berlaku harus diambil langsung dari `jenis_simpanan_id` yang tersimpan di `simpanan_anggota_<lokasi>.jenis_simpanan`.

---

## 6. Endpoint & alur

### 6.1 `GET /generate_simpanan`
- Controller: `GenerateBungaController::index`
- Middleware: `auth`
- View input: `resources/views/generate_simpanan/index.blade.php` (form ID)
- View progress: `resources/views/generate_simpanan/result.blade.php` (auto-submit 800 ms, looping `start += limit`)
- Loop utama: `Simpanan::whereIn('id', $ids)->skip($start)->take($perPage)->get()` → `RealSimpananGenerator::generateForCif($simpanan->id)`.

### 6.2 `GET /simpanan/generate`
- Controller: `SimpananController::generateSimpanan`
- Middleware: `auth`
- View: `resources/views/simpanan/generate.blade.php`
- Loop utama: chunk 25 CIF per halaman.

### 6.3 `POST /simpanan/generate/{cif}`
- Controller: `SimpananController::generateSimpanan2($cif)`
- Middleware: `auth` + CSRF
- Pemicu UI: tombol "Generate Real Simpanan" di `resources/views/simpanan/partials/detail.blade.php` (AJAX, SweetAlert)
- Response: JSON `{success, message, processed}` atau 500.

---

## 7. CLI: `php artisan simp:regenerate`

### 7.1 Opsi

| Opsi | Default | Arti |
|---|---|---|
| `--lokasi=` | (kosong) | Hanya proses kecamatan/lokasi tertentu (id). |
| `--cif=` | (kosong) | Hanya proses CIF tertentu. |
| `--dry-run` | false | Tidak menulis; hanya menampilkan rencana. |

### 7.2 Contoh

```bash
# Dry-run 1 CIF di lokasi 1
php artisan simp:regenerate --lokasi=1 --cif=123 --dry-run

# Re-generate semua CIF di lokasi 1
php artisan simp:regenerate --lokasi=1

# Re-generate 1 CIF saja
php artisan simp:regenerate --cif=123

# Re-generate semua lokasi & semua CIF
php artisan simp:regenerate
```

### 7.3 Alur

Untuk setiap `(lokasi, cif)`:
1. Cek tabel `simpanan_anggota_<lokasi>` ada.
2. Panggil `RealSimpananGenerator::generateForCif($cif, $lokasiOverride=$lokasi)`.
3. Tampilkan progress per-CIF.
4. Ringkasan: CIF dilihat, CIF berhasil, total transaksi diproses, daftar kegagalan.

---

## 8. Verifikasi sebelum/sesudah

### 8.1 Jumlah row per CIF (harus identik)

```sql
SELECT COUNT(*) FROM real_simpanan_<lokasi> WHERE cif = ?;
```

### 8.2 Saldo kumulatif (`sum`) per CIF (harus identik)

```sql
SELECT tgl_transaksi, idt, sum
FROM real_simpanan_<lokasi>
WHERE cif = ?
ORDER BY tgl_transaksi ASC, idt ASC;
```

### 8.3 Distribusi kode setelah re-generate

```sql
SELECT kode, COUNT(*)
FROM real_simpanan_<lokasi>
GROUP BY kode
ORDER BY kode;
```

### 8.4 Kode di luar jangkauan `kode_simp` (seharusnya kosong)

```sql
SELECT DISTINCT kode
FROM real_simpanan_<lokasi>
WHERE kode NOT IN (SELECT kode FROM kode_simp WHERE lokasi = 0)
  AND kode <> 0;
```

> Query ini sengaja mengecualikan `kode = 0` (lihat §4.4) sehingga transaksi yang gagal diklasifikasi tetap lolos tanpa terdeteksi. Cek terpisah:
> ```sql
> SELECT COUNT(*) FROM real_simpanan_<lokasi> WHERE kode = 0;
> ```

### 8.5 Kode yang belum dipetakan (seharusnya nol)

```sql
-- Semua id 1..15 sudah punya rule di KodeMutasiClassifier.
-- Query ini hanya untuk validasi pasca-refactor.
SELECT DISTINCT k.id
FROM kode_simp k
LEFT JOIN (
    SELECT id_mutasi FROM (
        VALUES ROW(1),ROW(2),ROW(3),ROW(4),ROW(5),ROW(6),ROW(7),
                    ROW(8),ROW(9),ROW(10),ROW(11),ROW(12),ROW(13),
                    ROW(14),ROW(15)
    ) AS m(id_mutasi)
) m ON m.id_mutasi = k.id
WHERE k.lokasi = 0 AND m.id_mutasi IS NULL;
```

---

## 9. Backup & roll-back

### 9.1 Backup sebelum eksekusi

```bash
mysqldump -h <host> -u <user> -p siupk_upk \
  real_simpanan_1 real_simpanan_2 real_simpanan_3 \
  > backup_real_simpan_$(date +%F).sql
```

### 9.2 Roll-back manual

```bash
mysql -h <host> -u <user> -p siupk_upk < backup_real_simpan_2026-08-12.sql
```

---

## 10. Dampak ke insert transaksi langsung

Beberapa titik insert `RealSimpanan::create([...])` di `SimpananController` sudah dialihkan dari hard-coded ke `KodeSimp::resolveKode()`:

| Lokasi (`SimpananController.php`) | Mutasi | `KodeSimp` konstanta | Fallback saat ini |
|---|---|---|---|
| `simpanTransaksi()` (setor)   | setor tunai        | `MUTASI_SETOR`         | `2` |
| `simpanTransaksi()` (tarik)   | tarik tunai        | `MUTASI_TARIK`         | `3` |
| `simpanBunga()` (bunga)       | bunga              | `MUTASI_BUNGA`         | `5` ¹ |
| `simpanBunga()` (pajak)       | pajak bunga        | `MUTASI_PAJAK`         | `6` ¹ |
| `simpanBunga()` (admin)       | admin bulanan      | `MUTASI_ADMIN`         | `7` ¹ |
| `store()` (setor awal)       | setor awal         | `MUTASI_SETOR_AWAL`    | `1` |
| `store()` (admin buka buku)  | admin buka rekening | `MUTASI_ADMIN`        | `7` ¹ |

> ¹ **Inkonsistensi historis**: fallback `5/6/7` di atas ditulis saat kode MD §3.2 lama menggunakan `5/6/7` untuk BUNGA/PAJAK/ADMIN. Setelah konstanta `KodeSimp` di-refactor ke `4/10/5` (lihat §3.2), fallback **belum diubah** untuk menjaga backward compatibility dengan data historis. Saat ini `KodeSimp::resolveKode()` mengembalikan nilai benar dari tabel (`kode=4` untuk bunga, `kode=10` untuk pajak, `kode=5` untuk admin) selama tabel `kode_simp` terisi sesuai §3.2. Fallback hanya terpakai bila tabel kosong.

---

## 11. Logging

Setiap pemanggilan `RealSimpananGenerator::generateForCif()` menulis:

```php
Log::info('RealSimpananGenerator.generateForCif', [
    'lokasi'    => $lokasi,
    'cif'       => $cif,
    'processed' => $processed,
]);
```

Periksa via `storage/logs/laravel.log` atau sistem log yang dikonfigurasi.

---

## 12. Lampiran: ID `kode_simp` aktif

| id | kode | def | nama_kode |
|---|---|---|---|
| 1  | 1  | 1  | Setoran Awal |
| 2  | 2  | 2  | Setoran Tunai |
| 3  | 3  | 3  | Penarikan Tunai |
| 4  | 4  | 4  | Bunga Simpanan |
| 5  | 5  | 5  | Biaya Admin |
| 6  | 6  | 6  | Transfer Masuk |
| 7  | 7  | 7  | Transfer Keluar |
| 8  | 8  | 8  | Koreksi Kredit |
| 9  | 9  | 9  | Koreksi Debet |
| 10 | 10 | 10 | Pajak Simpanan |
| 11 | 11 | 11 | Setoran Berjangka |
| 12 | 12 | 12 | Pencairan Deposito |
| 13 | 13 | 13 | Cetak Rekening Koran |
| 14 | 14 | 14 | Tutup Rekening |
| 15 | 15 | 15 | Pemindahbukuan |

> Untuk membuat override per lokasi, tambahkan baris baru dengan `lokasi = <id_kecamatan>` dan `kecuali = NULL` (atau CSV id lokasi yang dikecualikan jika ingin override terbatas).
