<?php

namespace App\View\Composers;

use App\Models\LaporanPaceklik;
use Illuminate\View\View;

//penanda laporan yang baru diteruskan ke BPBD
class IncomingReportComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();

        $count = $user && $user->role === 'bpbd'
            ? LaporanPaceklik::where('status', LaporanPaceklik::STATUS_MENUNGGU)
                ->whereDoesntHave('penetapanAktif')
                ->count()
            : 0;

        $view->with('incomingReportCount', $count);
    }
}