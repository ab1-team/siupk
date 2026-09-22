# PANDUAN TEKNIS DAN SPESIFIKASI PENYUSUNAN 4 LAPORAN KEUANGAN LKM
## Berdasarkan POJK Nomor 19/POJK.05/2021, POJK Nomor 41 Tahun 2024, dan POJK Nomor 49 Tahun 2024
---
*Dokumen ini merupakan panduan komprehensif, terstruktur, dan siap pakai untuk menyusun kertas kerja, basis data, dan laporan kepatuhan Lembaga Keuangan Mikro (LKM). Panduan ini memuat formula matematis, skema kolom basis data, logika perhitungan otomatis (Excel/Python), rujukan pasal hukum konkrit, dan perbandingan lintas regulasi.*

---

## DAFTAR LAPORAN YANG DIATUR
1. **Laporan 1: Laporan Kolektibilitas Pinjaman (POJK 19/2021 - Lama)**
2. **Laporan 2: Laporan Kolektibilitas Pinjaman (POJK 41/2024 - Baru)**
3. **Laporan 3: Laporan Penilaian Tingkat Kesehatan (POJK 19/2021 - Lama)**
4. **Laporan 4: Laporan Penilaian Tingkat Kesehatan & Status Pengawasan (POJK 41/2024 & POJK 49/2024 - Baru)**

---

# LAPORAN 1: LAPORAN KOLEKTIBILITAS PINJAMAN (POJK 19/2021)

### 1. Landasan Hukum & Ketentuan Pokok
*   **Kategori Kolektibilitas (3 Kelompok)**: Kualitas pinjaman atau pembiayaan wajib dinilai menjadi kelompok **Lancar, Diragukan, dan Macet** [59].
*   **Faktor Penilaian**: Ditentukan berdasarkan ketepatan pembayaran pokok dan/atau bunga/imbal hasil dengan batasan yang dibedakan menurut **Jenis Angsuran** nasabah [59].
*   **Penyisihan Penghapusan Pinjaman (PPAP) Wajib**:
    *   LKM yang mengelola Simpanan dan/atau Pinjaman diterima **di atas Rp200.000.000,00** wajib membentuk PPAP [63].
    *   Tarif PPAP Minimum: Lancar (0%), Diragukan (50%), Macet (100%) dari sisa pokok [63].
*   **Mitigasi Agunan**: PPAP untuk kualitas Diragukan dan Macet dihitung dari sisa pokok setelah dikurangi dengan nilai penjaminan kredit atau agunan yang diakui [66].

### 2. Parameter Pembobotan & Hari Tunggakan (POJK 19/2021)
Penetapan kualitas menggunakan parameter hari keterlambatan/tunggakan angsuran [142]:

| Kualitas / Kolektibilitas | Jenis Angsuran | Parameter Tunggakan Pokok & Bunga | Parameter Jatuh Tempo Pinjaman |
| :--- | :--- | :--- | :--- |
| **LANCAR** | Harian / Mingguan <br> Bulanan / Selapanan <br> Musiman | Tunggakan $\le$ 3 bulan <br> Tunggakan $\le$ 6 kali angsuran <br> Tunggakan 1 kali pembayaran | dan/atau <br> Jatuh tempo $\le$ 1 bulan |
| **DIRAGUKAN** | Harian / Mingguan <br> Bulanan / Selapanan <br> Musiman | Tunggakan > 3 bulan s.d. 6 bulan <br> Tunggakan > 6 kali s.d. 12 kali <br> Tunggakan 2 kali pembayaran | dan/atau <br> Jatuh tempo > 1 bulan s.d. 2 bulan |
| **MACET** | Harian / Mingguan <br> Bulanan / Selapanan <br> Musiman | Tunggakan > 6 bulan <br> Tunggakan > 12 kali angsuran <br> Tunggakan > 2 kali pembayaran | dan/atau <br> Jatuh tempo > 2 bulan |

*Catatan Khusus Mudharabah/Musyarakah (Syariah)*: 
*   **Diragukan**: Kriteria di atas terpenuhi, ATAU rasio Realisasi Bagi Hasil (RBH) terhadap Proyeksi Bagi Hasil (PBH) $\le 30\%$ selama 3 periode pembayaran [144].
*   **Macet**: Kriteria di atas terpenuhi, ATAU rasio RBH terhadap PBH $\le 30\%$ untuk lebih dari 3 periode pembayaran [145].

### 3. Struktur Kolom Database & Formula Excel (POJK 19/2021)
Untuk menyusun Laporan Kolektibilitas berdasarkan POJK 19/2021 di Excel, gunakan struktur kolom berikut:

| Kolom | Nama Kolom | Tipe Data | Deskripsi / Formula Excel |
| :---: | :--- | :---: | :--- |
| **A** | ID Debitur | Text | ID unik nasabah (contoh: PP-001) |
| **B** | Nama Debitur | Text | Nama lengkap nasabah |
| **C** | Jenis Angsuran | Dropdown | Pilihan: "Harian/Mingguan", "Bulanan", "Musiman" |
| **D** | Saldo Pokok (Outstanding) | Currency | Sisa pokok pinjaman berjalan (Rp) |
| **E** | Tunggakan Hari (NH) | Integer | Jumlah hari terlambat bayar pokok/bunga |
| **F** | Jumlah Bulan Tunggakan | Decimal | Formula: `=E/30` |
| **G** | Jumlah Angsuran Tertunggak | Integer | Input manual jumlah kali angsuran terlewati |
| **H** | Kolektibilitas | Text | **Formula Otomatis**: `=IF(C="Harian/Mingguan", IF(F<=3, "Lancar", IF(F<=6, "Diragukan", "Macet")), IF(C="Bulanan", IF(G<=6, "Lancar", IF(G<=12, "Diragukan", "Macet")), IF(G<=1, "Lancar", IF(G<=2, "Diragukan", "Macet"))))` |
| **I** | Nilai Agunan yang Sah | Currency | Nilai agunan setelah dikalikan batas haircut OJK (min. 120% loan value) [117] |
| **J** | Sisa Pokok setelah Agunan | Currency | **Formula**: `=MAX(0, D - I)` |
| **K** | % PPAP Wajib | Percentage | **Formula**: `=IF(H="Lancar", 0%, IF(H="Diragukan", 50%, 100%))` |
| **L** | PPAP Wajib Terbentuk | Currency | **Formula**: `=J * K` |

---

# LAPORAN 2: LAPORAN KOLEKTIBILITAS PINJAMAN (POJK 41/2024)

### 1. Landasan Hukum & Ketentuan Pokok
*   **Kategori Kolektibilitas (5 Kelompok)**: Kualitas pinjaman atau pembiayaan wajib dinilai menjadi kelompok **Lancar, Dalam Perhatian Khusus (DPK), Kurang Lancar, Diragukan, dan Macet** [4].
*   **Faktor Penilaian**: Menggunakan ketepatan pembayaran pokok dan/atau bunga/imbal hasil dengan parameter tunggakan yang **seragam berbasis hari kalender keterlambatan (Days Past Due/DPD)** tanpa memandang jenis angsuran [24].
*   **Penyisihan Penghapusan Pinjaman (PPAP) Wajib**:
    *   Wajib dibentuk oleh **seluruh LKM** tanpa ada batasan minimal nilai simpanan/pinjaman berjalan [107].
    *   Tarif PPAP Minimum: Lancar (0%), DPK (5%), Kurang Lancar (15%), Diragukan (50%), Macet (100%) dari sisa pokok [289].
*   **Asuransi Kredit**: Regulasi baru memperbolehkan pengalihan risiko pinjaman menggunakan **Asuransi Kredit** selain Penjaminan Kredit dan Agunan fisik untuk memotong PPAP [105, 281].

### 2. Parameter Tunggakan Hari Kalender (POJK 41/2024)
Penentuan kolektibilitas untuk seluruh jenis pinjaman konvensional dan piutang syariah [544, 545]:

1.  **LANCAR**: Tidak terdapat keterlambatan atau terdapat keterlambatan **s.d. 10 hari** [544].
2.  **DALAM PERHATIAN KHUSUS (DPK)**: Keterlambatan **> 10 hari s.d. 90 hari** [544, 545].
3.  **KURANG LANCAR**: Keterlambatan **> 90 hari s.d. 120 hari** [546].
4.  **DIRAGUKAN**: Keterlambatan **> 120 hari s.d. 180 hari** [546].
5.  **MACET**: Keterlambatan **lebih dari 180 hari** [546].

*Catatan Khusus Pembiayaan Mudharabah & Musyarakah (Bagi Hasil)* [547, 548]:
*   **Lancar**: Telat $\le$ 10 hari **dan/atau** Rasio RBH/PBH $\ge 80\%$.
*   **DPK**: Telat 11 - 90 hari **dan/atau** Rasio RBH/PBH > 50% s.d. < 80%.
*   **Kurang Lancar**: Telat 91 - 120 hari **dan/atau** Rasio RBH/PBH > 30% s.d. $\le 50\%$.
*   **Diragukan**: Telat 121 - 180 hari **dan/atau** Rasio RBH/PBH $\le 30\%$ selama 3 periode pembayaran.
*   **Macet**: Telat > 180 hari **dan/atau** Rasio RBH/PBH $\le 30\%$ untuk lebih dari 3 periode pembayaran.

### 3. Struktur Kolom Database & Formula Excel (POJK 41/2024)
Rancang struktur tabel laporan kolektibilitas baru yang terstandardisasi sebagai berikut:

| Kolom | Nama Kolom | Tipe Data | Deskripsi / Formula Excel |
| :---: | :--- | :---: | :--- |
| **A** | ID Debitur | Text | ID unik nasabah (contoh: LOAN-48) |
| **B** | Nama Debitur | Text | Nama lengkap nasabah sesuai dokumen identitas |
| **C** | Saldo Pokok (Outstanding) | Currency | Sisa pokok kewajiban nasabah (Rp) |
| **D** | Hari Tunggakan (DPD) | Integer | Jumlah hari terlambat dihitung dari tanggal jatuh tempo |
| **E** | Kolektibilitas | Text | **Formula Otomatis**: `=IF(D<=10, "Lancar", IF(D<=90, "DPK", IF(D<=120, "Kurang Lancar", IF(D<=180, "Diragukan", "Macet"))))` |
| **F** | Jenis Agunan | Dropdown | Deposito / HT / Non-HT / Tanah Adat / Kendaraan / Asuransi |
| **G** | Nilai Taksasi Agunan | Currency | Nilai taksiran pasar yang sah dari agunan fisik/penjaminan |
| **H** | % Pengakuan OJK | Percentage | **Formula**: `=IF(F="Deposito", 100%, IF(F="HT", 80%, IF(F="Non-HT", 60%, IF(F="Tanah Adat", 50%, IF(F="Kendaraan", 50%, 0%)))))` [108] |
| **I** | Agunan Diperhitungkan | Currency | **Formula**: `=G * H` |
| **J** | Sisa Pokok setelah Agunan | Currency | **Formula**: `=MAX(0, C - I)` [290] |
| **K** | % PPAP Wajib | Percentage | **Formula**: `=IF(E="Lancar", 0%, IF(E="DPK", 5%, IF(E="Kurang Lancar", 15%, IF(E="Diragukan", 50%, 100%))))` [289] |
| **L** | PPAP Wajib Terbentuk | Currency | **Formula**: `=J * K` [290] |

---

# LAPORAN 3: LAPORAN PENILAIAN TINGKAT KESEHATAN (POJK 19/2021)

### 1. Landasan Hukum & Ketentuan Pokok
*   **Metodologi Pengukuran**: Tingkat kesehatan LKM secara ketat hanya dinilai berdasarkan **2 (dua) indikator rasio keuangan utama**, yaitu Likuiditas dan Solvabilitas [68, 17].
*   **Ketentuan Batas Minimum (Threshold)**:
    1.  **Rasio Likuiditas**: Wajib dipelihara paling rendah **4% (empat persen)** [69].
    2.  **Rasio Solvabilitas**: Wajib dipelihara paling rendah **110% (seratus sepuluh persen)** [70].
*   **Batas Kewajiban Ekuitas**: LKM wajib menjaga Ekuitas paling rendah **75%** dari Modal Disetor (untuk PT) atau simpanan pokok+wajib+hibah (untuk Koperasi) [73].
*   **Status Kesulitan Membahayakan**: Jika Rasio Likuiditas jatuh **< 3%** dan Rasio Solvabilitas jatuh **< 100%**, LKM dinyatakan masuk ke dalam kondisi kritis yang membahayakan kelangsungan usaha [87].

### 2. Rumus dan Formula Rasio Keuangan (POJK 19/2021)

#### A. Rasio Likuiditas (%)
$$\text{Rasio Likuiditas} = \frac{\text{Kas} + \text{Setara Kas}}{\text{Liabilitas Lancar}} \times 100\%$$
*   *Versi Syariah (LKMS)*: Penyebut ditambah dengan "Dana Syirkah Temporer (DST) kurang dari 1 tahun" [70].
*   *Formula Excel*: `= (Kas_dan_Setara_Kas / Liabilitas_Lancar) * 100`

#### B. Rasio Solvabilitas (%)
$$\text{Rasio Solvabilitas} = \frac{\text{Total Aset}}{\text{Total Liabilitas}} \times 100\%$$
*   *Versi Syariah (LKMS)*: Penyebut ditambah dengan "Total Dana Syirkah Temporer" [71].
*   *Formula Excel*: `= (Total_Aset / Total_Liabilitas) * 100`

#### C. Rasio Ekuitas (%)
$$\text{Rasio Ekuitas} = \frac{\text{Total Ekuitas}}{\text{Modal Disetor / Komponen Modal Koperasi}} \times 100\%$$
*   *Formula Excel*: `= (Total_Ekuitas / Modal_Disetor) * 100`

### 3. Format Template Laporan Tingkat Kesehatan POJK 19/2021
Berikut adalah susunan pelaporan ringkasan tingkat kesehatan:

```text
========================================================================================
                     LAPORAN TINGKAT KESEHATAN LEMBAGA KEUANGAN MIKRO
                                 (POJK NO. 19/POJK.05/2021)
========================================================================================
Nama LKM       : [Nama LKM]
Periode Lapor  : [Periode Lapor, Contoh: Caturwulan II - Agustus 2021]
----------------------------------------------------------------------------------------

I. INFORMASI NERACA UTAMA
   1. Kas dan Setara Kas                : Rp _______________________
   2. Total Aset                        : Rp _______________________
   3. Liabilitas Lancar                 : Rp _______________________
   4. Total Liabilitas                  : Rp _______________________
   5. Modal Disetor                     : Rp _______________________
   6. Total Ekuitas                     : Rp _______________________

II. ANALISIS RASIO KEUANGAN & KEPATUHAN
   ----------------------------------------------------------------------------------
   No  Indikator Rasio          Formula            Batas POJK   Hasil (%)   Status
   ----------------------------------------------------------------------------------
   1.  Rasio Likuiditas      (Kas/Liab Lancar)      Min. 4%     ________%   [Memenuhi/TM]
   2.  Rasio Solvabilitas    (Aset/Liabilitas)      Min. 110%   ________%   [Memenuhi/TM]
   3.  Rasio Ekuitas         (Ekuitas/Modal Disetor) Min. 75%    ________%   [Memenuhi/TM]
   ----------------------------------------------------------------------------------

III. KESIMPULAN STATUS KESEHATAN
   * Pilihan Status: [SEHAT / KONDISI MEMBAHAYAKAN KELANGSUNGAN USAHA]
   * Catatan Penjelas: LKM masuk status membahayakan jika Likuiditas < 3% dan Solvabilitas < 100%.

                                                          Jakarta, _____________________
                                                          Direksi LKM,

                                                          ( ___________________________ )
```

---

# LAPORAN 4: LAPORAN PENILAIAN TINGKAT KESEHATAN (POJK 41/2024 & POJK 49/2024)

### 1. Landasan Hukum & Ketentuan Pokok
*   **Aspek Penilaian Komprehensif**: Penilaian TKS LKM diperluas menjadi **5 (lima) aspek/faktor** utama: **Permodalan & Solvabilitas, Kualitas Aset, Rentabilitas, Likuiditas, dan Manajemen** [26].
*   **Peringkat Komposit (PK)**: Hasil akhir penilaian dinyatakan dalam Peringkat Komposit 1 sampai 5 [308, 309]:
    *   **PK 1 (Sangat Sehat)**: LKM sangat mampu menghadapi pengaruh negatif luar [309].
    *   **PK 2 (Sehat)**: LKM dinilai mampu menghadapi pengaruh bisnis [310].
    *   **PK 3 (Cukup Sehat)**: Batas minimal wajib TKS yang harus dipelihara LKM [300].
    *   **PK 4 (Kurang Sehat)**: Menunjukkan LKM memiliki kelemahan struktural serius [310].
    *   **PK 5 (Tidak Sehat)**: Mengalami kesulitan fatal yang mengancam kelangsungan usaha [311].
*   **Integrasi Status Pengawasan (POJK 49/2024)**: Penggabungan nilai peringkat komposit dan rasio keuangan kuantitatif secara otomatis menentukan status pengawasan LKM: **Normal, Intensif, atau Khusus** [13].

### 2. Parameter Rasio Utama & Pembobotan Skor (SEOJK 21/2015)
Untuk menetapkan skor angka (0-100), OJK mengacu pada parameter kuantitatif pendukung berikut:

*   **Rasio Solvabilitas (Aset/Liabilitas)**: Batas Kepatuhan Min. 110% [302]. Bobot aspek: **12.5%** [675].
*   **Rasio Ekuitas (Ekuitas/Modal Disetor)**: Batas Kepatuhan Min. 75% [301]. Bobot aspek: **12.5%** [675].
*   **Rasio Kualitas Aset (NPL/NPF Neto)**: Batas Kepatuhan Max. 5% [33]. Bobot aspek: **21%** [675].
    *   *Rumus NPL Neto*:
        $$\text{NPL Neto} = \frac{\text{Kredit Bermasalah (Kurang Lancar + Diragukan + Macet)} - \text{PPAP Terbentuk} - \text{Agunan}}{\text{Total Outstanding Pinjaman}} \times 100\%$$ [304]
*   **Coverage PPAP**: Perbandingan PPAP Terbentuk dengan PPAP Wajib OJK (Min. 100%). Bobot aspek: **14%** [675].
*   **Rasio Rentabilitas (ROA)**: Kemampuan LKM menghasilkan laba operasional secara positif (Min. Positif). Bobot aspek: **10%** [675].
*   **Rasio Likuiditas (Kas/Liabilitas Lancar)**: Batas Kepatuhan Min. 4% [306]. Bobot aspek: **10%** [675].
*   **Manajemen**: Penilaian kualitatif (Asumsi Normal). Bobot aspek: **20%** [675].

### 3. Logika Penetapan Status Pengawasan (POJK 49/2024 & POJK 41/2024)
Status pengawasan ditetapkan secara otomatis di kertas kerja dengan aturan logika berikut [164, 166]:

1.  **PENGAWASAN INTENSIF**:
    *   Tingkat Kesehatan LKM berada pada **Peringkat Komposit 4 (PK 4)** [349]; **dan/atau**
    *   **Parameter Kuantitatif**:
        *   Rasio Ekuitas terhadap Modal Disetor: **$\ge 50\%$ s.d. $< 75\%$** [349]; **dan/atau**
        *   Rasio Kualitas Piutang Pinjaman Bermasalah Neto (NPL Neto): **$> 5\%$ s.d. $< 25\%$** [349].
2.  **PENGAWASAN KHUSUS**:
    *   LKM tidak dapat disehatkan setelah status pengawasan intensif berakhir [352]; **dan/atau**
    *   Tingkat Kesehatan LKM berada pada **Peringkat Komposit 5 (PK 5)** [353]; **dan/atau**
    *   **Parameter Kuantitatif**:
        *   Rasio Ekuitas terhadap Modal Disetor: **$< 50\%$** [353]; **dan/atau**
        *   Rasio Kualitas Piutang Pinjaman Bermasalah Neto (NPL Neto): **$\ge 25\%$** [353].

### 4. Format Laporan Penilaian Kesehatan & Status Pengawasan (2024)
Rancang draf kertas kerja Laporan TKS LKM komprehensif seperti contoh format berikut:

```text
PT. LKM [NAMA LKM]
KECAMATAN [KECAMATAN] KABUPATEN [KABUPATEN]
========================================================================================
             LAPORAN PENILAIAN TINGKAT KESEHATAN DAN STATUS PENGAWASAN
                    PERIODE BERJALAN: SEPTEMBER 2026
========================================================================================

A. PARAMETER KEUANGAN UTAMA (DATA RIIL INPUT)
   1. Total Aset                                : Rp _______________________
   2. Total Liabilitas                          : Rp _______________________
   3. Kas & Setara Kas                          : Rp _______________________
   4. Liabilitas Lancar                         : Rp _______________________
   5. Modal Disetor                             : Rp _______________________
   6. Total Ekuitas                             : Rp _______________________
   7. Total Outstanding Pinjaman                : Rp _______________________
   8. Sektor Bermasalah (KL + D + M)            : Rp _______________________
   9. Nilai Agunan yang Sah Terikat             : Rp _______________________
  10. Cadangan PPAP yang Dibentuk               : Rp _______________________

B. MATRIKS ANALISIS RASIO KUANTITATIF & SKORING (SEOJK 21/2015)
   ---------------------------------------------------------------------------------------------
   No  Faktor Penilaian        Rasio       Bobot    Hasil (%)  Batas POJK   Skor   Status
   ---------------------------------------------------------------------------------------------
   1.  Permodalan &           Solvabilitas 12,5%    ________%   Min. 110%   ____  [Memenuhi]
       Solvabilitas           Ekuitas      12,5%    ________%   Min. 75%    ____  [Memenuhi]
   2.  Kualitas Aset          NPL Neto     21,0%    ________%   Max. 5%     ____  [Memenuhi]
                              Cover PPAP   14,0%    ________%   Min. 100%   ____  [Memenuhi]
   3.  Rentabilitas           ROA          10,0%    ________%   Positif     ____  [Sangat Baik]
   4.  Likuiditas             Kas/Liab Lcr 10,0%    ________%   Min. 4%     ____  [Memenuhi]
   5.  Manajemen              Kualitatif   20,0%    [Kualitatif] Baik       ____  [Normal]
   ---------------------------------------------------------------------------------------------
       SKOR KOMPOSIT AKHIR (TOTAL TERTIMBANG)                           [____]
   ---------------------------------------------------------------------------------------------

C. KLASIFIKASI PERINGKAT KOMPOSIT KESEHATAN (PK)
   [ ] PK 1 (Sangat Sehat) : Skor Komposit Akhir 81 - 100
   [ ] PK 2 (Sehat)        : Skor Komposit Akhir 66 - <81
   [ ] PK 3 (Cukup Sehat)  : Skor Komposit Akhir 51 - <66
   [ ] PK 4 (Kurang Sehat) : Skor Komposit Akhir <51 (atau trigger khusus)
   [ ] PK 5 (Tidak Sehat)  : NPL Neto >= 25% atau Ekuitas < 50% atau Cover PPAP < 50%

   * PROYEKSI PERINGKAT KOMPOSIT: PK [___]

D. KEPUTUSAN STATUS PENGAWASAN (POJK 49/2024)
   * PENGAWASAN NORMAL : Memenuhi seluruh rasio kuantitatif POJK & Peringkat Komposit 1-3.
   * PENGAWASAN INTENSIF: PK 4 ATAU Rasio Ekuitas 50% s.d <75% ATAU NPL Neto 5% s.d <25%.
   * PENGAWASAN KHUSUS : PK 5 ATAU Rasio Ekuitas <50% ATAU NPL Neto >= 25%.

   * STATUS PENGAWASAN SAAT INI: [ PENGAWASAN NORMAL / INTENSIF / KHUSUS ]

                                                          Klirong, 30 September 2026
   Disetujui Oleh,                                        Dilaporkan Oleh,


   ( etin prihatin, S.A.P. )                             ( Teguh Priyandono, S.Si. )
   Direktur Utama                                         Bagian Umum & Keuangan
```

---

# MATRIKS KOMPARASI UTAMA (PERBEDAAN POJK 2021 VS 2024)

| Aspek Penilaian | POJK 19/POJK.05/2021 (Regulasi Lama) | POJK 41/2024 & POJK 49/2024 (Regulasi Baru) |
| :--- | :--- | :--- |
| **Kolektibilitas Pinjaman** | Terdiri atas **3 Kategori** (Lancar, Diragukan, Macet) [59]. | Diperluas menjadi **5 Kategori** (Lancar, DPK, Kurang Lancar, Diragukan, Macet) [4]. |
| **Parameter Tunggakan** | **Sangat rumit**, dibedakan rinci menurut frekuensi jenis angsuran nasabah (harian, bulanan, musiman) [142]. | **Sederhana & Seragam**, murni berbasis kumulatif hari kalender terlambat *(Days Past Due/DPD)* [24]. |
| **Aspek Penilaian TKS** | Hanya menilai **2 Aspek Utama** (Rasio Likuiditas & Solvabilitas) [17, 68]. | Komprehensif berbasis **5 Aspek** (Permodalan, Kualitas Aset, Rentabilitas, Likuiditas, Manajemen) [26, 300]. |
| **Batas Maksimal NPL** | LKM wajib menjaga rasio pinjaman bermasalah maksimal **10%** (berbasis NPL Bruto) [61]. | Penilaian beralih menggunakan **NPL Neto dengan batas maksimal diperketat menjadi 5%** [33]. |
| **Agunan dalam Rasio NPL**| Agunan fisik **tidak berpengaruh** pada perhitungan persentase NPL LKM. | Nilai agunan fisik & asuransi kredit **diakui sebagai pengurang langsung** dalam perhitungan Rasio NPL Neto [304]. |
| **Status Pengawasan** | Tidak terintegrasi secara modular, LKM bermasalah hanya dikirimi surat pembinaan umum [61, 71]. | Terintegrasi modular melalui **3 status pengawasan** (Normal, Intensif, Khusus) dengan tindakan penyehatan wajib [13]. |
