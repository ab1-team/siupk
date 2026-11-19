<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Manajemen logo kabupaten untuk tenant (UPK).
 *
 * Halaman ini memakai guard `web`/`auth` seperti /dashboard. Logo dipilih
 * lewat dropdown kabupaten, lalu disimpan deterministik sebagai
 * logo_kab/{id_kabupaten}.png dan dipakai sebagai kop/cover dokumen
 * perguliran (proposal, pencairan, rekomendasi kredit, dll).
 */
class LogoKabController extends Controller
{
    /**
     * Folder tujuan di storage/app/public.
     */
    private const FOLDER = 'logo_kab';

    /**
     * Ekstensi wajib hasil akhir.
     */
    private const EKSTENSI = '.png';

    /**
     * Batas dimensi terpanjang (px).
     */
    private const MAKS_DIMENSI = 1200;

    /**
     * Batas jumlah piksel sebelum gambar didecode, untuk mencegah
     * decompression bomb (mis. PNG kecil dengan dimensi ekstrem).
     */
    private const MAKS_PIKSEL = 40000000;

    /**
     * Alasan kegagalan terakhir saat menyimpan file.
     */
    private ?string $alasan = null;

    public function index(Request $request)
    {
        $kabupaten = Kabupaten::orderBy('nama_kab')->get(['id', 'kd_kab', 'nama_kab']);

        // Default: kabupaten milik tenant yang sedang login (kalau ada).
        $default = $this->kabupatenTenant();
        $selectedId = (int) old(
            'kabupaten_id',
            $request->query('kabupaten', $default?->id)
        );

        return view('logo-kab.index', [
            'title' => 'Logo Kabupaten',
            'kabupaten' => $kabupaten,
            'selectedId' => $selectedId,
            'petaLogo' => $this->petaLogo(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $this->validated($request);
        $id = (int) $data['kabupaten_id'];
        $namaFile = $this->namaFile($id);

        if (!$this->simpan($request->file('file'), $id)) {
            return back()->withInput()->with('error', "Logo {$namaFile} gagal disimpan. " . $this->alasanGagal());
        }

        return redirect()->route('logoKab.index', ['kabupaten' => $id])
            ->with('status', "Logo {$namaFile} berhasil disimpan.");
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'kabupaten_id' => ['required', 'integer', Rule::exists('kabupaten', 'id')],
        ], [
            'kabupaten_id.required' => 'Kabupaten wajib dipilih.',
            'kabupaten_id.exists' => 'Kabupaten tidak ditemukan.',
        ]);

        $id = (int) $data['kabupaten_id'];
        $namaFile = $this->namaFile($id);
        $ada = $this->adaFile($id);

        Storage::disk('public')->delete($this->path($id));

        $pesan = $ada
            ? "Logo {$namaFile} berhasil dihapus."
            : "Logo {$namaFile} memang belum ada.";

        return redirect()->route('logoKab.index', ['kabupaten' => $id])->with('status', $pesan);
    }

    /**
     * Validasi input form.
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'kabupaten_id' => ['required', 'integer', Rule::exists('kabupaten', 'id')],
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp,bmp', 'max:4096'],
        ], [
            'kabupaten_id.required' => 'Kabupaten wajib dipilih.',
            'kabupaten_id.integer' => 'Kabupaten tidak valid.',
            'kabupaten_id.exists' => 'Kabupaten tidak ditemukan.',
            'file.required' => 'File gambar wajib diunggah.',
            'file.image' => 'File yang diunggah harus berupa gambar.',
            'file.mimes' => 'Format gambar harus JPG, PNG, GIF, WEBP, atau BMP.',
            'file.max' => 'Ukuran gambar maksimal 4 MB.',
        ]);
    }

    /**
     * Kabupaten milik tenant yang sedang login, dipakai sebagai pilihan awal.
     *
     * Tenant = UPK = kecamatan. Session `lokasi` berisi ID kecamatan
     * (diisi oleh AuthController saat login). Jika session tidak ada,
     * pakai lokasi milik user yang sedang login.
     */
    private function kabupatenTenant(): ?Kabupaten
    {
        $lokasi = Session::get('lokasi') ?: (auth()->user()->lokasi ?? null);

        if (!$lokasi) {
            return null;
        }

        $kec = Kecamatan::where('id', $lokasi)->first();

        return $kec
            ? Kabupaten::where('kd_kab', $kec->kd_kab)->first()
            : null;
    }

    /**
     * Daftar logo yang sudah ada: [id_kabupaten => waktu modifikasi].
     */
    private function petaLogo(): array
    {
        $disk = Storage::disk('public');

        if (!$disk->exists(self::FOLDER)) {
            return [];
        }

        $peta = [];

        foreach ($disk->files(self::FOLDER) as $path) {
            $file = basename($path);

            if (preg_match('/^(\d+)' . preg_quote(self::EKSTENSI, '/') . '$/', $file, $match)) {
                $peta[(int) $match[1]] = $disk->lastModified($path);
            }
        }

        return $peta;
    }

    /**
     * Path relatif file di disk public, contoh: logo_kab/12.png
     */
    private function path(int $id): string
    {
        return self::FOLDER . '/' . $this->namaFile($id);
    }

    /**
     * Nama file hasil akhir, contoh: 12 -> 12.png
     */
    private function namaFile(int $id): string
    {
        return $id . self::EKSTENSI;
    }

    /**
     * Cek apakah file logo sudah ada.
     */
    private function adaFile(int $id): bool
    {
        return Storage::disk('public')->exists($this->path($id));
    }

    /**
     * Simpan file gambar sebagai PNG dengan nama {id}.png
     */
    private function simpan(UploadedFile $file, int $id): bool
    {
        $disk = Storage::disk('public');
        $target = $this->path($id);

        $this->alasan = null;

        // Folder tujuan harus ada sebelum menulis. Kesalahan permission
        // ditangkap dan ditampilkan sebagai pesan form, bukan 500.
        if (!$this->siapkanFolder($disk)) {
            $this->alasan = 'Folder ' . storage_path('app/public/' . self::FOLDER) . ' tidak bisa dibuat atau ditulis. Periksa permission folder storage.';

            return false;
        }

        // Cek dimensi sebelum decode untuk menghindari konsumsi memori GD
        // berlebihan pada gambar dengan dimensi ekstrem.
        $ukuran = @getimagesize($file->getRealPath());
        if ($ukuran && ($ukuran[0] * $ukuran[1]) > self::MAKS_PIKSEL) {
            $this->alasan = 'Dimensi gambar terlalu besar. Maksimal ' . number_format(self::MAKS_PIKSEL, 0, ',', '.') . ' piksel.';

            return false;
        }

        $image = $this->bacaGambar($file->get());

        if (!$image) {
            $this->alasan = 'Gambar tidak bisa dikonversi ke PNG.';

            return false;
        }

        $image = $this->skalaTurun($image);

        ob_start();
        $berhasil = imagepng($image);
        $isi = ob_get_clean();

        imagedestroy($image);

        if (!$berhasil || $isi === false) {
            $this->alasan = 'Gambar tidak bisa dikonversi ke PNG.';

            return false;
        }

        try {
            $ok = $disk->put($target, $isi);
        } catch (\Throwable $e) {
            // Kegagalan menulis (permission, disk penuh, dll) ditampilkan
            // sebagai pesan di form, bukan sebagai error 500.
            report($e);

            $this->alasan = 'File tidak bisa ditulis ke folder storage. Periksa permission folder storage.';

            return false;
        }

        if (!$ok) {
            $this->alasan = 'File tidak bisa ditulis ke folder storage. Periksa permission folder storage.';
        }

        return $ok;
    }

    /**
     * Pastikan folder logo_kab ada dan bisa ditulis.
     */
    private function siapkanFolder($disk): bool
    {
        try {
            if (!$disk->exists(self::FOLDER)) {
                $disk->makeDirectory(self::FOLDER);
            }

            return $disk->exists(self::FOLDER);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Alasan kegagalan terakhir, untuk ditampilkan di form.
     */
    private function alasanGagal(): string
    {
        return $this->alasan ?: 'Gambar tidak bisa dikonversi ke PNG.';
    }

    /**
     * Baca gambar dari string binary.
     *
     * imagecreatefromstring() mendeteksi format otomatis (JPG, PNG, GIF,
     * WEBP, BMP) sehingga tidak perlu cabang per format.
     */
    private function bacaGambar(string $isi)
    {
        if ($isi === '' || !function_exists('imagecreatefromstring')) {
            return false;
        }

        return @imagecreatefromstring($isi);
    }

    /**
     * Perkecil gambar yang melebihi batas dimensi.
     */
    private function skalaTurun(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= self::MAKS_DIMENSI && $height <= self::MAKS_DIMENSI) {
            return $image;
        }

        $skala = min(self::MAKS_DIMENSI / $width, self::MAKS_DIMENSI / $height);
        $newWidth = max(1, (int) round($width * $skala));
        $newHeight = max(1, (int) round($height * $skala));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        imagedestroy($image);

        return $resized;
    }
}
