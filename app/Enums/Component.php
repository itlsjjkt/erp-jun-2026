<?php

namespace App\Enums;

enum Component: string
{
    case NO_SI = 'no_si';
    case TGL_TERIMA_INVOICE = 'tgl_terima_invoice';
    case TGL_INVOICE = 'tgl_invoice';
    case PERIODE_TEMPO = 'periode_tempo';
    case TGL_SURAT_JALAN = 'tgl_surat_jalan';
    case TGL_JATUH_TEMPO = 'tgl_jatuh_tempo';
    case JUMLAH = 'jumlah';
    case NAMA_SUPPLIER = 'nama_supplier';
    case NO_PR = 'no_pr';
    case TGL_PR = 'tgl_pr';
    case NO_PO = 'no_po';
    case TGL_PO = 'tgl_po';
    case INVOICE = 'invoice';
    case FAKTUR_PAJAK = 'faktur_pajak';
    case NILAI_INVOICE = 'nilai_invoice';
    case NILAI_FAKTUR_PAJAK = 'nilai_faktur_pajak';
    case FILE_INVOICE = 'file_invoice';
    case FILE_FAKTUR_PAJAK = 'file_faktur_pajak';
    case DETAIL_NOTES = 'detail_notes';
    case PROFORMA_INVOICE = 'proforma_invoice';
    case NILAI_PROFORMA_INVOICE = 'nilai_proforma_invoice';
    case FILE_PROFORMA_INVOICE = 'file_proforma_invoice';

    public function label(): string
    {
        return match($this) {
            self::NO_SI => 'No SI',
            self::TGL_TERIMA_INVOICE => 'Tgl Terima Invoice',
            self::TGL_INVOICE => 'Tgl Invoice',
            self::PERIODE_TEMPO => 'Periode Tempo',
            self::TGL_SURAT_JALAN => 'Tgl Surat Jalan',
            self::TGL_JATUH_TEMPO => 'Tgl Jatuh Tempo',
            self::JUMLAH => 'Jumlah',
            self::NAMA_SUPPLIER => 'Nama Supplier',
            self::NO_PR => 'No PR',
            self::TGL_PR => 'Tgl PR',
            self::NO_PO => 'No PO',
            self::TGL_PO => 'Tgl PO',
            self::INVOICE => 'Invoice',
            self::FAKTUR_PAJAK => 'Faktur Pajak',
            self::NILAI_INVOICE => 'Nilai Invoice',
            self::NILAI_FAKTUR_PAJAK => 'Nilai Faktur Pajak',
            self::FILE_INVOICE => 'File Invoice',
            self::FILE_FAKTUR_PAJAK => 'File Faktur Pajak',
            self::DETAIL_NOTES => 'Detail Notes',
            self::PROFORMA_INVOICE => 'Proforma Invoice',
            self::NILAI_PROFORMA_INVOICE => 'Nilai Proforma Invoice',
            self::FILE_PROFORMA_INVOICE => 'File Proforma Invoice',
        };
    }
}
