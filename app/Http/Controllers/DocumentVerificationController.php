<?php

namespace App\Http\Controllers;

use App\Models\Lpb;
use App\Models\Bpb;
use Illuminate\Support\Facades\DB;

/**
 * Halaman verifikasi publik dokumen (diakses lewat scan QR, tanpa login).
 * Token yang dipakai adalah kolom `uuid` pada tabel lpb / bpb.
 */
class DocumentVerificationController extends Controller
{
    /**
     * Verifikasi LPB (Laporan Penerimaan Barang).
     */
    public function lpb($uuid)
    {
        $row = DB::table('lpb')->select('id')->where('uuid', $uuid)->first();

        if (! $row) {
            abort(404);
        }

        $lpb       = Lpb::getByID($row->id);
        $lpb_items = Lpb::getProductItem($row->id);

        return view('verify.lpb', compact('lpb', 'lpb_items'));
    }

    /**
     * Verifikasi BPB. Satu tabel `bpb` memuat dua jenis:
     *  - BPB Jakarta  : punya spb_id (dibuat dari SPB)
     *  - BPB Lokal    : punya po_id  (Franco, dibuat langsung dari PO)
     * Jenis dideteksi otomatis dari baris datanya sehingga tidak bisa salah rute.
     */
    public function bpb($uuid)
    {
        $row = DB::table('bpb')->select('id', 'spb_id')->where('uuid', $uuid)->first();

        if (! $row) {
            abort(404);
        }

        if (! empty($row->spb_id)) {
            // BPB Jakarta
            $bpb       = Bpb::getByID($row->id);
            $bpb_items = Bpb::getProductItemPublic($row->id);

            return view('verify.bpb', compact('bpb', 'bpb_items'));
        }

        // BPB Lokal (Franco) — blade-nya memakai relasi Eloquent, jadi pakai model.
        $bpb       = Bpb::findOrFail($row->id);
        $bpb_items = Bpb::getProductFrancoItem($row->id);

        return view('verify.bpb_lokal', compact('bpb', 'bpb_items'));
    }
}
