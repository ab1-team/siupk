<?php

namespace Tests\Feature;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * End-to-end fitur upload logo kabupaten untuk tenant (/logo-kab).
 *
 * Halaman memakai guard `web`/`auth` seperti /dashboard. Kabupaten dipilih
 * lewat dropdown, lalu logo disimpan deterministik sebagai
 * logo_kab/{id_kabupaten}.png dan dipakai oleh kop/cover dokumen perguliran.
 */
class LogoKabUploadTest extends TestCase
{
    use DatabaseTransactions;

    private Kecamatan $kec;

    private Kabupaten $kab;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->kab = Kabupaten::orderBy('id')->first();

        if (!$this->kab) {
            $this->markTestSkipped('Tidak ada data kabupaten untuk diuji.');
        }

        $this->kec = Kecamatan::where('kd_kab', $this->kab->kd_kab)->first();

        if (!$this->kec) {
            $this->markTestSkipped('Tidak ada UPK/kecamatan untuk kabupaten uji.');
        }
    }

    private function loginTenant(?int $lokasi = null): void
    {
        $lokasi = $lokasi ?: $this->kec->id;

        $user = new User;
        $user->uname = 'uji-logo-kab';
        $user->namadepan = 'Uji';
        $user->namabelakang = 'Tenant';
        $user->lokasi = $lokasi;
        $user->level = 1;
        $user->hak_akses = '';
        $user->akses_menu = '';
        $user->akses_tombol = '';

        $this->actingAs($user, 'web');

        $this->withSession([
            'lokasi' => $lokasi,
            'menu' => collect(),
            'tombol' => [],
            'logo' => null,
            'nama' => 'Uji Tenant',
            'nama_lembaga' => 'UPK Uji',
            'foto' => null,
            'angsuran' => false,
            'unpaidInvoice' => 0,
        ]);
    }

    private function filePngPalsu(): UploadedFile
    {
        // PNG 1x1 valid (base64).
        $data = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        return UploadedFile::fake()->createWithContent('logo.png', $data);
    }

    private function fileJpgPalsu(): UploadedFile
    {
        // JPEG 1x1 valid.
        $data = base64_decode(
            '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0a'
            . 'HBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAA'
            . 'AAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q=='
        );

        return UploadedFile::fake()->createWithContent('logo.jpg', $data);
    }

    public function test_halaman_terlindungi_dari_tamu(): void
    {
        // Guard web mengarahkan tamu ke halaman login utama.
        $this->get('/logo-kab')->assertRedirect('/');
    }

    public function test_tenant_bisa_membuka_halaman_dan_melihat_dropdown_kabupaten(): void
    {
        $this->loginTenant();

        $response = $this->get('/logo-kab');

        $response->assertStatus(200);
        $response->assertSee('Logo Kabupaten');
        $response->assertSee('kabupaten_id');
        $response->assertSee('-- Pilih Kabupaten --');
        $response->assertSee($this->kab->nama_kab);
    }

    public function test_upload_png_disimpan_sebagai_id_kabupaten_terpilih(): void
    {
        $this->loginTenant();

        $response = $this->put('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
            'file' => $this->filePngPalsu(),
        ]);

        $response->assertRedirect('/logo-kab?kabupaten=' . $this->kab->id);
        $response->assertSessionHas('status');

        Storage::disk('public')->assertExists('logo_kab/' . $this->kab->id . '.png');
    }

    public function test_upload_jpg_dikonversi_menjadi_png_asli(): void
    {
        $this->loginTenant();

        $this->put('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
            'file' => $this->fileJpgPalsu(),
        ])->assertRedirect('/logo-kab?kabupaten=' . $this->kab->id);

        Storage::disk('public')->assertExists('logo_kab/' . $this->kab->id . '.png');

        $path = Storage::disk('public')->path('logo_kab/' . $this->kab->id . '.png');
        $this->assertSame('image/png', mime_content_type($path), 'File hasil harus benar-benar PNG.');
        $this->assertNotFalse(@getimagesize($path), 'File hasil harus bisa dibaca sebagai gambar.');
    }

    public function test_upload_tanpa_file_ditolak(): void
    {
        $this->loginTenant();

        $response = $this->put('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
        ]);

        $response->assertSessionHasErrors('file');
        Storage::disk('public')->assertMissing('logo_kab/' . $this->kab->id . '.png');
    }

    public function test_kabupaten_wajib_dipilih(): void
    {
        $this->loginTenant();

        $response = $this->put('/logo-kab', [
            'file' => $this->filePngPalsu(),
        ]);

        $response->assertSessionHasErrors('kabupaten_id');
        Storage::disk('public')->assertMissing('logo_kab/' . $this->kab->id . '.png');
    }

    public function test_kabupaten_harus_ada(): void
    {
        $this->loginTenant();

        $tidakAda = ((int) Kabupaten::max('id')) + 999;

        $response = $this->put('/logo-kab', [
            'kabupaten_id' => $tidakAda,
            'file' => $this->filePngPalsu(),
        ]);

        $response->assertSessionHasErrors('kabupaten_id');
        Storage::disk('public')->assertMissing('logo_kab/' . $tidakAda . '.png');
    }

    public function test_file_bukan_gambar_ditolak(): void
    {
        $this->loginTenant();

        $response = $this->put('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
            'file' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('file');
        Storage::disk('public')->assertMissing('logo_kab/' . $this->kab->id . '.png');
    }

    public function test_upload_ulang_menimpa_file_lama(): void
    {
        $this->loginTenant();

        $this->put('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
            'file' => $this->filePngPalsu(),
        ]);

        $path = Storage::disk('public')->path('logo_kab/' . $this->kab->id . '.png');
        $isiLama = file_get_contents($path);

        $dataBaru = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        );

        $this->put('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
            'file' => UploadedFile::fake()->createWithContent('logo.png', $dataBaru),
        ])->assertRedirect('/logo-kab?kabupaten=' . $this->kab->id);

        $file = Storage::disk('public')->files('logo_kab');
        $this->assertCount(1, $file, 'Upload ulang harus menimpa, bukan membuat file tambahan.');
        $this->assertSame('logo_kab/' . $this->kab->id . '.png', $file[0], 'Nama file harus tetap {id}.png.');
        $this->assertNotSame($isiLama, file_get_contents($path), 'File lama harus tertimpa.');
    }

    public function test_gambar_besar_diskala_turun(): void
    {
        $this->loginTenant();

        // PNG besar (3000x2000) harus diperkecil supaya file tidak membengkak.
        $lebar = 3000;
        $tinggi = 2000;
        $image = imagecreatetruecolor($lebar, $tinggi);
        imagefilledrectangle($image, 0, 0, $lebar - 1, $tinggi - 1, imagecolorallocate($image, 30, 30, 30));
        ob_start();
        imagepng($image);
        $isi = ob_get_clean();
        imagedestroy($image);

        $this->put('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
            'file' => UploadedFile::fake()->createWithContent('besar.png', $isi),
        ])->assertRedirect('/logo-kab?kabupaten=' . $this->kab->id);

        $path = Storage::disk('public')->path('logo_kab/' . $this->kab->id . '.png');
        [$w, $h] = getimagesize($path);

        $this->assertLessThanOrEqual(1200, $w, 'Lebar gambar harus dibatasi.');
        $this->assertLessThanOrEqual(1200, $h, 'Tinggi gambar harus dibatasi.');
        $this->assertSame(1200, $w, 'Lebar harus proporsional (3000 -> 1200).');
        $this->assertSame(800, $h, 'Tinggi harus proporsional (2000 -> 800).');
    }

    public function test_hapus_logo_menghapus_file(): void
    {
        $this->loginTenant();

        $this->put('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
            'file' => $this->filePngPalsu(),
        ]);

        Storage::disk('public')->assertExists('logo_kab/' . $this->kab->id . '.png');

        $this->delete('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
        ])->assertRedirect('/logo-kab?kabupaten=' . $this->kab->id);

        Storage::disk('public')->assertMissing('logo_kab/' . $this->kab->id . '.png');
    }

    public function test_model_kabupaten_mendeteksi_logo_yang_sudah_diunggah(): void
    {
        $this->loginTenant();

        $kab = Kabupaten::find($this->kab->id);
        $this->assertFalse($kab->logoKabTersedia());

        $this->put('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
            'file' => $this->filePngPalsu(),
        ])->assertRedirect('/logo-kab?kabupaten=' . $this->kab->id);

        $this->assertTrue(Kabupaten::find($this->kab->id)->logoKabTersedia());
    }

    public function test_logo_disimpan_untuk_kabupaten_yang_dipilih(): void
    {
        $this->loginTenant();

        $kabLain = Kabupaten::where('id', '!=', $this->kab->id)->orderBy('id')->first();

        if (!$kabLain) {
            $this->markTestSkipped('Tidak ada kabupaten kedua untuk menguji pilihan dropdown.');
        }

        $this->put('/logo-kab', [
            'kabupaten_id' => $kabLain->id,
            'file' => $this->filePngPalsu(),
        ])->assertRedirect('/logo-kab?kabupaten=' . $kabLain->id);

        // Hanya file kabupaten yang dipilih yang ditulis.
        Storage::disk('public')->assertExists('logo_kab/' . $kabLain->id . '.png');
        Storage::disk('public')->assertMissing('logo_kab/' . $this->kab->id . '.png');

        // Halaman menampilkan nama file & ID kabupaten yang dipilih.
        $response = $this->get('/logo-kab?kabupaten=' . $kabLain->id);
        $response->assertStatus(200);
        $response->assertSee($kabLain->nama_kab);
        $response->assertSee($kabLain->id . '.png');
    }

    /**
     * Regression test untuk bug 500 Internal Server Error.
     *
     * Storage::put() melempar exception League\Flysystem\UnableToCreateDirectory
     * yang TIDAK ditangkap Laravel, sehingga halaman harus menampilkan pesan
     * error di form (bukan 500).
     */
    public function test_exception_dari_storage_tidak_menghasilkan_500(): void
    {
        $this->loginTenant();

        $disk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $disk->shouldReceive('exists')->with('logo_kab')->andReturn(true);
        $disk->shouldReceive('put')
            ->andThrow(new \League\Flysystem\UnableToCreateDirectory('Tidak bisa membuat folder.'));

        Storage::shouldReceive('disk')->with('public')->andReturn($disk);

        $response = $this->put('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
            'file' => $this->filePngPalsu(),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    /**
     * Folder tujuan dibuat otomatis kalau belum ada (fresh deploy).
     */
    public function test_folder_tujuan_dibuat_otomatis_kalau_belum_ada(): void
    {
        $this->loginTenant();

        Storage::disk('public')->assertMissing('logo_kab');

        $this->put('/logo-kab', [
            'kabupaten_id' => $this->kab->id,
            'file' => $this->filePngPalsu(),
        ])->assertRedirect('/logo-kab?kabupaten=' . $this->kab->id);

        Storage::disk('public')->assertExists('logo_kab/' . $this->kab->id . '.png');
    }
}
