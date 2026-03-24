<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor = Vendor::query()->first() ?? Vendor::factory()->create();

        foreach ($this->catalog() as $categoryName => $products) {
            $category = Category::query()->updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName]
            );

            foreach ($products as $productData) {
                $product = Product::query()->updateOrCreate(
                    ['slug' => Str::slug($productData['name'])],
                    [
                        'vendor_id' => $vendor->id,
                        'category_id' => $category->id,
                        'name' => $productData['name'],
                        'description' => $productData['description'],
                        'price' => $productData['price'],
                        'weight' => $productData['weight'],
                        'status' => ProductStatus::Active,
                    ]
                );

                ProductImage::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'image' => 'products/product_placeholder.jpg',
                    ],
                    ['is_primary' => true]
                );

                ProductVariant::query()->updateOrCreate(
                    ['sku' => $productData['sku']],
                    [
                        'product_id' => $product->id,
                        'variant_name' => $productData['variant_name'],
                        'price' => $productData['price'],
                        'stock' => $productData['stock'],
                    ]
                );
            }
        }
    }

    /**
     * @return array<string, array<int, array<string, int|string>>>
     */
    protected function catalog(): array
    {
        return [
            'Kerajinan Lokal' => [
                [
                    'name' => 'Tas Anyaman Pandan Lombok',
                    'description' => 'Tas anyaman tangan dari daun pandan kering dengan motif khas Lombok, cocok dipakai untuk aktivitas santai atau hampers produk lokal.',
                    'price' => 185000,
                    'weight' => 650,
                    'variant_name' => 'Ukuran 30 x 25 cm',
                    'sku' => 'KER-001',
                    'stock' => 18,
                ],
                [
                    'name' => 'Vas Gerabah Kasongan',
                    'description' => 'Vas gerabah dari tanah liat bakar dengan finishing natural, pas untuk dekorasi ruang tamu, meja kerja, atau sudut rumah bernuansa tradisional.',
                    'price' => 145000,
                    'weight' => 1800,
                    'variant_name' => 'Tinggi 25 cm',
                    'sku' => 'KER-002',
                    'stock' => 12,
                ],
                [
                    'name' => 'Ukiran Kayu Jepara Mini',
                    'description' => 'Hiasan ukir kayu berukuran mini dengan detail halus khas Jepara, cocok untuk souvenir, dekorasi meja, atau hadiah bernilai seni.',
                    'price' => 210000,
                    'weight' => 900,
                    'variant_name' => 'Motif Daun 20 cm',
                    'sku' => 'KER-003',
                    'stock' => 9,
                ],
                [
                    'name' => 'Tenun Ikat Troso Meteran',
                    'description' => 'Kain tenun ikat handmade dari Troso dengan warna hangat dan motif etnik, cocok untuk bahan busana, selendang, atau dekorasi interior.',
                    'price' => 275000,
                    'weight' => 400,
                    'variant_name' => 'Panjang 2 meter',
                    'sku' => 'KER-004',
                    'stock' => 14,
                ],
            ],
            'Sayur dan Buah' => [
                [
                    'name' => 'Bayam Hijau Segar 500 Gram',
                    'description' => 'Bayam hijau segar yang dipanen pada hari yang sama, cocok untuk sayur bening, tumisan, atau campuran menu sehat keluarga.',
                    'price' => 12000,
                    'weight' => 500,
                    'variant_name' => 'Kemasan 500 gram',
                    'sku' => 'SBU-001',
                    'stock' => 40,
                ],
                [
                    'name' => 'Wortel Organik 1 Kg',
                    'description' => 'Wortel organik bertekstur renyah dan rasa manis alami, cocok untuk sop, jus, salad, atau stok sayur harian di rumah.',
                    'price' => 28000,
                    'weight' => 1000,
                    'variant_name' => 'Kemasan 1 kg',
                    'sku' => 'SBU-002',
                    'stock' => 35,
                ],
                [
                    'name' => 'Jeruk Manis Medan 1 Kg',
                    'description' => 'Jeruk Medan dengan rasa manis segar dan aroma khas, enak dimakan langsung atau dijadikan jus untuk konsumsi harian.',
                    'price' => 35000,
                    'weight' => 1000,
                    'variant_name' => 'Kemasan 1 kg',
                    'sku' => 'SBU-003',
                    'stock' => 28,
                ],
                [
                    'name' => 'Pisang Cavendish Premium 1 Sisir',
                    'description' => 'Pisang Cavendish premium dengan tingkat kematangan bertahap, praktis untuk camilan sehat, sarapan, atau olahan dessert.',
                    'price' => 32000,
                    'weight' => 1200,
                    'variant_name' => '1 sisir isi 8-10 buah',
                    'sku' => 'SBU-004',
                    'stock' => 22,
                ],
            ],
            'Makanan Ringan' => [
                [
                    'name' => 'Keripik Pisang Cokelat 250 Gram',
                    'description' => 'Keripik pisang tipis dan renyah dengan balutan rasa cokelat yang manis, cocok untuk teman santai atau oleh-oleh ringan.',
                    'price' => 22000,
                    'weight' => 250,
                    'variant_name' => 'Pouch 250 gram',
                    'sku' => 'MKR-001',
                    'stock' => 50,
                ],
                [
                    'name' => 'Peyek Kacang Renyah 200 Gram',
                    'description' => 'Peyek gurih berbahan tepung beras dan kacang tanah pilihan, pas untuk camilan keluarga atau pelengkap lauk rumahan.',
                    'price' => 18000,
                    'weight' => 200,
                    'variant_name' => 'Toples 200 gram',
                    'sku' => 'MKR-002',
                    'stock' => 45,
                ],
                [
                    'name' => 'Kacang Bawang Gurih 300 Gram',
                    'description' => 'Kacang bawang panggang dengan aroma bawang yang kuat dan tekstur garing, cocok disajikan saat kumpul keluarga atau hari raya.',
                    'price' => 26000,
                    'weight' => 300,
                    'variant_name' => 'Standing pouch 300 gram',
                    'sku' => 'MKR-003',
                    'stock' => 38,
                ],
                [
                    'name' => 'Stik Talas Balado 150 Gram',
                    'description' => 'Camilan stik talas renyah dengan bumbu balado pedas manis, pas untuk pencinta makanan ringan bercita rasa kuat.',
                    'price' => 17000,
                    'weight' => 150,
                    'variant_name' => 'Pouch 150 gram',
                    'sku' => 'MKR-004',
                    'stock' => 42,
                ],
            ],
            'Pakaian dan Aksesoris' => [
                [
                    'name' => 'Kaos Katun Motif Nusantara',
                    'description' => 'Kaos katun combed yang adem dipakai dengan sentuhan motif Nusantara, cocok untuk aktivitas harian dengan gaya kasual lokal.',
                    'price' => 89000,
                    'weight' => 250,
                    'variant_name' => 'Size M',
                    'sku' => 'PKA-001',
                    'stock' => 30,
                ],
                [
                    'name' => 'Hijab Voal Polos Premium',
                    'description' => 'Hijab voal polos berbahan ringan dan mudah dibentuk, nyaman dipakai untuk kerja, kuliah, maupun acara semi formal.',
                    'price' => 45000,
                    'weight' => 120,
                    'variant_name' => 'Ukuran 115 x 115 cm',
                    'sku' => 'PKA-002',
                    'stock' => 36,
                ],
                [
                    'name' => 'Tote Bag Kanvas Etnik',
                    'description' => 'Tote bag kanvas tebal dengan aksen motif etnik pada bagian depan, cocok untuk membawa perlengkapan harian dengan tampilan simpel.',
                    'price' => 78000,
                    'weight' => 300,
                    'variant_name' => 'Ukuran 35 x 40 cm',
                    'sku' => 'PKA-003',
                    'stock' => 20,
                ],
                [
                    'name' => 'Gelang Manik Handmade',
                    'description' => 'Gelang manik buatan tangan dengan perpaduan warna netral, cocok sebagai aksesoris harian atau hadiah sederhana bernuansa handmade.',
                    'price' => 25000,
                    'weight' => 50,
                    'variant_name' => 'All Size',
                    'sku' => 'PKA-004',
                    'stock' => 60,
                ],
            ],
            'Produk Digital' => [
                [
                    'name' => 'Template CV ATS Profesional',
                    'description' => 'Template CV siap edit dengan format rapi dan ramah ATS, cocok untuk lamaran kerja magang, fresh graduate, maupun profesional.',
                    'price' => 39000,
                    'weight' => 0,
                    'variant_name' => 'File DOCX & PDF',
                    'sku' => 'DIG-001',
                    'stock' => 999,
                ],
                [
                    'name' => 'Ebook Resep Jajanan Rumahan',
                    'description' => 'Ebook berisi kumpulan resep jajanan rumahan lengkap dengan bahan dan langkah praktis, cocok untuk pemula maupun usaha kecil.',
                    'price' => 55000,
                    'weight' => 0,
                    'variant_name' => 'File PDF 80 halaman',
                    'sku' => 'DIG-002',
                    'stock' => 999,
                ],
                [
                    'name' => 'Preset Lightroom Warm Market',
                    'description' => 'Preset foto bernuansa hangat untuk konten produk, kuliner, dan lifestyle agar tampilan feed lebih konsisten dan menarik.',
                    'price' => 49000,
                    'weight' => 0,
                    'variant_name' => '3 File XMP',
                    'sku' => 'DIG-003',
                    'stock' => 999,
                ],
                [
                    'name' => 'Undangan Digital Minimalis',
                    'description' => 'Template undangan digital dengan desain minimalis yang mudah disunting, cocok untuk acara ulang tahun, syukuran, atau pernikahan sederhana.',
                    'price' => 65000,
                    'weight' => 0,
                    'variant_name' => 'Format Canva',
                    'sku' => 'DIG-004',
                    'stock' => 999,
                ],
            ],
        ];
    }
}
