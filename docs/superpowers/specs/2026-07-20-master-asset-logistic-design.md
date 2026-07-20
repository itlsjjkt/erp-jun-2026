# Master Asset (Logistic) — Desain Sub-Project #1

Tanggal: 2026-07-20
Status: disetujui user (brainstorming per bagian)

## Latar Belakang

Modul `inventory_asset` (Logistic) berperan ganda: register unit asset (master) sekaligus
dokumen perolehan (DIA) dan posisi terkini. Keputusan: dipertegas menjadi **Master Asset**
memakai **Pendekatan 1 — evolusi di tempat** (tabel & route tetap, peran dipertegas).

Scope keseluruhan dipecah 4 sub-project; dokumen ini hanya #1:

1. **Master Asset** (dokumen ini): restrukturisasi peran + atribut garansi + reorganisasi menu + migrasi
2. Serah terima & mutasi lokasi
3. Peminjaman & service
4. Disposal & stock opname asset

Integrasi accounting (tabel `assets` + depresiasi) di luar scope, jadi kandidat sub-project lanjutan.

## Bagian 1 — Struktur Data & Siklus Status

Tabel `inventory_assets` tetap jadi tabel fisik. Pengelompokan kolom:

- **Identitas** (terkunci setelah aktif): `doc_no`, `uuid`, `product_id`, `company_id`, `measure`, `image`
- **Perolehan** (boleh dikoreksi selama draft): `parent_inventory_asset_id`, `price`, `type_relation`,
  `relation_item_id`, `attachment`, `notes`
- **Garansi** (kolom baru, semua nullable): `purchase_date` (date), `warranty_until` (date),
  `vendor_name` (varchar — sengaja teks bebas, bukan FK supplier)
- **Posisi terkini** (nanti hanya diubah transaksi #2–4): `location_id`, `department_id`,
  `user_asset_id`, `user_asset_nik`, `status`

Siklus status (integer, kolom tidak berubah tipe):

| Nilai | Status | Keterangan |
|---|---|---|
| 0 | draft | baru dibuat, boleh diedit/hapus |
| 1 | tersedia | aktif, siap dipakai/dirujuk transaksi |
| 2 | diserahkan | dipegang user (sub-project #2) |
| 3 | dipinjam | sub-project #3 |
| 4 | service | sub-project #3 |
| 9 | disposed | final (sub-project #4) |

Mapping data lama: `0 Draft→0 draft`, `1 Publish→1 tersedia`, `2 Deleted→soft delete via deleted_at`.
Di sub-project #1 transisi yang tersedia hanya **draft→tersedia**. Edit manual lokasi/pemegang
hanya saat draft; setelah tersedia terkunci menunggu modul transaksi.

## Bagian 2 — Menu & UI

Grup menu "Inventory Asset" → **"Asset"** (tetap di Logistic, gate `inventory_asset` tetap):

- **Master Asset** ← "Inventory Asset Item" (index `inventory_asset`, jadi menu utama)
- **Dokumen Perolehan (DIA)** ← "Daftar Inventory Asset" (index `parent_inventory_asset`)
- **Master User Asset** ← diaktifkan lagi (dibutuhkan sub-project #2)

Route/nama route tidak berubah. Index: judul "Master Asset", filter status + company, badge status baru,
tombol aksi mengikuti status (draft: edit/hapus/aktifkan; lainnya: lihat + print QR).
Form create/edit: tambah 3 field garansi (opsional); pola create massal via DIA + `create_instant` tetap.
Show & show_noauth (scan QR): tampilkan blok garansi, status, pemegang/lokasi terkini.
Print QR tidak diubah (SVG, 24/36 mm).

## Bagian 3 — Dampak, Migrasi, Pengujian

Konsumen lain hanya membaca (`ShipHrTransferController` API sync HR, halaman scan QR publik
`inventory_asset/{id}/{uuid}`) — aman tanpa perubahan; badge ikut helper baru.
Helper `getStatusInventoryAsset()` dan `getStatusDia()` diperbarui (helpers dimuat via require_once,
tanpa composer dump).

Migrasi satu file: (1) tambah 3 kolom garansi nullable; (2) baris status 2 di-soft-delete.
Tanpa rename tabel/kolom.

Pengujian: feature test kecil untuk aturan kunci edit + transisi status; checklist manual:
create DIA + garansi → aktifkan → edit terkunci → print QR 24/36 → scan no-auth → filter index →
API sync HR → menu baru.
