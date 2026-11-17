<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\LogStatusLaporan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// PBI-34: menyimpan ke tabel log_status_laporan
class LogStatusLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function buatLaporan(): LaporanPaceklik
    {
        $rt = User::factory()->create(['role' => 'rt']);

        return LaporanPaceklik::create([
            'user_id'             => $rt->id,
            'alamat'              => 'Jl. Contoh No. 1',
            'jumlah_kk_terdampak' => 10,
        ]);
    }

    public function test_laporan_baru_dicatat_sebagai_status_awal(): void
    {
        $laporan = $this->buatLaporan();

        $this->assertDatabaseHas('log_status_laporan', [
            'laporan_id'  => $laporan->id,
            'status_lama' => null,
            'status_baru' => 1,
            'keterangan'  => 'Laporan dikirim',
        ]);
        $this->assertCount(1, $laporan->logStatus);
    }

    public function test_perubahan_status_dicatat_dengan_status_lama_dan_baru(): void
    {
        $laporan = $this->buatLaporan();

        $laporan->status = 2;
        $laporan->keteranganStatus = 'Diverifikasi oleh petugas BPBD';
        $laporan->save();

        $this->assertDatabaseHas('log_status_laporan', [
            'laporan_id'  => $laporan->id,
            'status_lama' => 1,
            'status_baru' => 2,
            'keterangan'  => 'Diverifikasi oleh petugas BPBD',
        ]);
        $this->assertSame(2, LogStatusLaporan::where('laporan_id', $laporan->id)->count());
    }

    public function test_keterangan_hanya_berlaku_untuk_satu_perubahan(): void
    {
        $laporan = $this->buatLaporan();

        $laporan->status = 2;
        $laporan->keteranganStatus = 'Catatan pertama';
        $laporan->save();

        $laporan->status = 3;
        $laporan->save();

        $this->assertNull($laporan->logStatus->last()->keterangan);
    }

    public function test_perubahan_kolom_selain_status_tidak_menambah_log(): void
    {
        $laporan = $this->buatLaporan();

        $laporan->alamat = 'Alamat yang sudah diperbaiki';
        $laporan->jumlah_kk_terdampak = 99;
        $laporan->save();

        $this->assertSame(1, LogStatusLaporan::where('laporan_id', $laporan->id)->count());
    }

    public function test_petugas_diisi_nama_akun_yang_login(): void
    {
        $laporan = $this->buatLaporan();
        $bpbd = User::factory()->create(['role' => 'bpbd', 'name' => 'Admin BPBD']);

        $this->actingAs($bpbd);
        $laporan->status = 2;
        $laporan->save();

        $this->assertDatabaseHas('log_status_laporan', [
            'laporan_id' => $laporan->id,
            'status_baru' => 2,
            'petugas'    => 'Admin BPBD',
        ]);
    }

    public function test_riwayat_berurutan_lewat_relasi_logstatus(): void
    {
        $laporan = $this->buatLaporan();

        $laporan->status = 2;
        $laporan->save();
        $laporan->status = 3;
        $laporan->save();

        $urutan = $laporan->fresh()->logStatus->map(fn ($log) => [$log->status_lama, $log->status_baru]);

        $this->assertSame([[null, 1], [1, 2], [2, 3]], $urutan->all());
    }

    public function test_log_ikut_terhapus_saat_laporan_dihapus(): void
    {
        $laporan = $this->buatLaporan();
        $laporan->status = 2;
        $laporan->save();

        $laporan->delete();

        $this->assertDatabaseCount('log_status_laporan', 0);
    }

    public function test_seeder_style_updateorcreate_tanpa_perubahan_status_tidak_menambah_log(): void
    {
        $laporan = $this->buatLaporan();

        LaporanPaceklik::updateOrCreate(
            ['kode' => $laporan->kode],
            ['alamat' => $laporan->alamat, 'jumlah_kk_terdampak' => 10, 'status' => 1]
        );

        $this->assertSame(1, LogStatusLaporan::where('laporan_id', $laporan->id)->count());
    }
}