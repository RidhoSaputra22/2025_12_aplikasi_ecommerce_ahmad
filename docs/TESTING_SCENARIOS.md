# Bukti Pengujian Skenario E-Commerce

Dokumen ini memetakan 63 skenario uji manual ke pengujian otomatis. Seluruh
pengujian menggunakan database terisolasi melalui `RefreshDatabase`, sehingga
tidak mengubah data aplikasi yang sedang digunakan.

## Cara menjalankan

Jalankan hanya 63 skenario:

```bash
composer test:scenarios
```

Jalankan seluruh pengujian project:

```bash
composer test
```

## Hasil verifikasi terakhir

Tanggal verifikasi: 31 Juli 2026.

```text
Tests: 74 passed (257 assertions)
Duration: 5.86s
```

Dari hasil tersebut, 63 pengujian adalah skenario pada dokumen acuan dan 11
pengujian lainnya adalah pengujian unit/integrasi project yang sudah ada.

## Pemetaan customer (1–21)

File: `tests/Feature/Scenarios/CustomerScenarioTest.php`

| No | Alur yang dibuktikan | Method pengujian |
|---:|---|---|
| 1 | Registrasi customer dengan data lengkap | `test_01_registrasi_customer_dengan_data_lengkap` |
| 2 | Registrasi customer dengan data kosong ditolak | `test_02_registrasi_customer_dengan_data_kosong_ditolak` |
| 3 | Login customer valid menuju homepage | `test_03_login_customer_valid_mengarah_ke_homepage` |
| 4 | Login customer tidak valid ditolak | `test_04_login_customer_tidak_valid_ditolak` |
| 5 | Homepage menampilkan navigasi dan banner | `test_05_homepage_menampilkan_navigasi_dan_banner` |
| 6 | Pencarian menampilkan produk sesuai kata kunci | `test_06_pencarian_produk_menampilkan_kata_kunci_yang_sesuai` |
| 7 | Filter produk berdasarkan kategori | `test_07_filter_produk_berdasarkan_kategori` |
| 8 | Filter produk berdasarkan rentang harga | `test_08_filter_produk_berdasarkan_harga` |
| 9 | Detail produk menampilkan informasi produk | `test_09_detail_produk_menampilkan_informasi_produk` |
| 10 | Produk, varian, dan jumlah masuk ke keranjang | `test_10_produk_varian_dan_jumlah_ditambahkan_ke_keranjang` |
| 11 | Perubahan jumlah memperbarui total keranjang | `test_11_perubahan_jumlah_memperbarui_keranjang_dan_total` |
| 12 | Item dapat dihapus dari keranjang | `test_12_item_dapat_dihapus_dari_keranjang` |
| 13 | Checkout lengkap membuat order | `test_13_checkout_data_lengkap_membuat_order` |
| 14 | Checkout tidak lengkap ditolak | `test_14_checkout_data_tidak_lengkap_ditolak` |
| 15 | Checkout multi-vendor membagi order per vendor | `test_15_checkout_multi_vendor_membagi_order_per_vendor` |
| 16 | Pembayaran diproses dan status diperbarui | `test_16_pembayaran_memproses_dan_memperbarui_status` |
| 17 | Detail order menampilkan status pembayaran terbaru | `test_17_detail_order_menampilkan_status_pembayaran_terbaru` |
| 18 | Riwayat hanya menampilkan order customer terkait | `test_18_riwayat_pesanan_hanya_menampilkan_order_customer` |
| 19 | Detail pesanan menampilkan rincian order | `test_19_detail_pesanan_menampilkan_rincian_order` |
| 20 | Tracking menampilkan status pengiriman | `test_20_tracking_customer_menampilkan_status_pengiriman` |
| 21 | Logout mengakhiri sesi dan kembali ke login | `test_21_logout_customer_mengakhiri_sesi_dan_kembali_ke_login` |

## Pemetaan vendor (22–42)

File: `tests/Feature/Scenarios/VendorScenarioTest.php`

| No | Alur yang dibuktikan | Method pengujian |
|---:|---|---|
| 22 | Registrasi vendor dengan data lengkap | `test_22_registrasi_vendor_dengan_data_lengkap` |
| 23 | Registrasi vendor dengan data kosong ditolak | `test_23_registrasi_vendor_dengan_data_kosong_ditolak` |
| 24 | Login vendor valid menuju dashboard | `test_24_login_vendor_valid_mengarah_ke_dashboard_vendor` |
| 25 | Login vendor tidak valid ditolak | `test_25_login_vendor_tidak_valid_ditolak` |
| 26 | Dashboard menampilkan ringkasan vendor | `test_26_dashboard_vendor_menampilkan_ringkasan_data` |
| 27 | Vendor menambahkan produk baru | `test_27_vendor_menambahkan_produk_baru` |
| 28 | Produk dengan data kosong ditolak | `test_28_produk_vendor_dengan_data_kosong_ditolak` |
| 29 | Vendor memperbarui data produk | `test_29_vendor_mengubah_data_produk` |
| 30 | Vendor menghapus produk | `test_30_vendor_menghapus_produk_tanpa_riwayat_order` |
| 31 | Daftar hanya menampilkan produk milik vendor | `test_31_daftar_produk_hanya_menampilkan_produk_vendor_terkait` |
| 32 | Vendor menambah dan memperbarui varian | `test_32_vendor_menambah_dan_memperbarui_varian_produk` |
| 33 | Daftar order hanya menampilkan order vendor terkait | `test_33_vendor_melihat_daftar_order_masuk_miliknya` |
| 34 | Detail order menampilkan rincian | `test_34_vendor_melihat_detail_order` |
| 35 | Order terbayar dapat diproses | `test_35_vendor_memproses_order_yang_sudah_dibayar` |
| 36 | Data pengiriman dan nomor resi tersimpan | `test_36_vendor_mengisi_data_pengiriman_dan_nomor_resi` |
| 37 | Status shipment dapat diperbarui menjadi tiba | `test_37_vendor_mengubah_status_shipment_menjadi_tiba` |
| 38 | Tracking vendor menampilkan informasi pengiriman | `test_38_vendor_melihat_tracking_pengiriman` |
| 39 | Wallet menampilkan saldo dan riwayat transaksi | `test_39_wallet_vendor_menampilkan_saldo_dan_riwayat_transaksi` |
| 40 | Rekening vendor dapat ditambah dan diperbarui | `test_40_vendor_menambah_dan_mengubah_rekening` |
| 41 | Laporan order vendor dapat diunduh sebagai PDF | `test_41_vendor_melihat_laporan_order_pdf` |
| 42 | Logout mengakhiri sesi vendor | `test_42_logout_vendor_mengakhiri_sesi` |

## Pemetaan admin (43–63)

File: `tests/Feature/Scenarios/AdminScenarioTest.php`

| No | Alur yang dibuktikan | Method pengujian |
|---:|---|---|
| 43 | Login admin valid menuju dashboard | `test_43_login_admin_valid_mengarah_ke_dashboard_admin` |
| 44 | Login admin tidak valid ditolak | `test_44_login_admin_tidak_valid_ditolak` |
| 45 | Dashboard menampilkan ringkasan sistem | `test_45_dashboard_admin_dapat_diakses_dan_menampilkan_ringkasan` |
| 46 | Admin melihat data customer | `test_46_admin_melihat_data_customer` |
| 47 | Admin menambah customer beserta role dan password aman | `test_47_admin_menambah_data_customer_dengan_role_dan_password_terenkripsi` |
| 48 | Admin memperbarui data customer | `test_48_admin_mengubah_data_customer` |
| 49 | Admin menghapus data customer | `test_49_admin_menghapus_data_customer` |
| 50 | Admin melihat data vendor | `test_50_admin_melihat_data_vendor` |
| 51 | Admin menambah vendor dan menetapkan role vendor | `test_51_admin_menambah_data_vendor_dan_menetapkan_role_vendor` |
| 52 | Admin memperbarui data vendor | `test_52_admin_mengubah_data_vendor` |
| 53 | Admin menghapus data vendor | `test_53_admin_menghapus_data_vendor` |
| 54 | Admin melihat data produk | `test_54_admin_melihat_data_produk` |
| 55 | Admin menambah, mengubah, dan menghapus kategori | `test_55_admin_menambah_mengubah_dan_menghapus_kategori` |
| 56 | Admin mengubah dan menghapus produk | `test_56_admin_mengubah_dan_menghapus_produk` |
| 57 | Admin melihat seluruh order | `test_57_admin_melihat_data_order` |
| 58 | Admin melihat detail transaksi order | `test_58_admin_melihat_detail_transaksi_order` |
| 59 | Admin memantau data pembayaran | `test_59_admin_memantau_data_pembayaran` |
| 60 | Admin melihat data shipment | `test_60_admin_melihat_data_shipment` |
| 61 | Admin melihat laporan order | `test_61_admin_melihat_laporan_order` |
| 62 | Admin mencairkan payment vendor dan memperbarui saldo | `test_62_admin_mencairkan_payment_vendor` |
| 63 | Logout mengakhiri sesi admin | `test_63_logout_admin_mengakhiri_sesi` |

## Catatan penyesuaian alur

- Data pembayaran admin diverifikasi pada tabel order karena arsitektur saat ini
  menampilkan status dan gateway pembayaran sebagai bagian dari resource order.
- Shipment dibuat saat checkout; vendor kemudian melengkapi nomor resi dan
  mengubah status pengirimannya.
- Gateway pembayaran pada skenario customer menggunakan implementasi palsu yang
  deterministik. Integrasi signature webhook Midtrans tetap diverifikasi oleh
  pengujian integrasi project yang terpisah.
