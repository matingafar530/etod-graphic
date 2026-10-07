<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Color;
use App\Models\PrintTemplate;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EtodDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'پوشیدنی', 'slug' => 'wearables'], ['name' => 'نوشیدنی', 'slug' => 'drinkware'],
            ['name' => 'رومیزی', 'slug' => 'tabletop'], ['name' => 'قاب موبایل', 'slug' => 'phone-cases'],
            ['name' => 'پارچه‌ای', 'slug' => 'fabric'], ['name' => 'هدیه‌ای', 'slug' => 'gifts'],
            ['name' => 'دکور', 'slug' => 'decor'],
        ])->mapWithKeys(fn ($item) => [$item['slug'] => Category::create($item + ['is_active' => true])]);

        $colors = collect([
            ['name' => 'سفید', 'slug' => 'white', 'hex_code' => '#FFFFFF'], ['name' => 'مشکی', 'slug' => 'black', 'hex_code' => '#111111'],
            ['name' => 'قرمز', 'slug' => 'red', 'hex_code' => '#EF4444'], ['name' => 'آبی', 'slug' => 'blue', 'hex_code' => '#3B82F6'],
            ['name' => 'سبز', 'slug' => 'green', 'hex_code' => '#22C55E'], ['name' => 'زرد', 'slug' => 'yellow', 'hex_code' => '#FACC15'],
            ['name' => 'صورتی', 'slug' => 'pink', 'hex_code' => '#F472B6'], ['name' => 'خاکستری', 'slug' => 'gray', 'hex_code' => '#9CA3AF'],
        ])->mapWithKeys(fn ($item) => [$item['slug'] => Color::create($item)]);

        $sizes = collect(['S', 'M', 'L', 'XL', '2XL', '3XL', '11oz', '15oz', '20oz', '500ml', 'iPhone 13', 'iPhone 14', 'iPhone 15', 'Galaxy A54', 'Galaxy S23', '22×18cm', '9×9cm', '37×42cm', '40×40cm', '45×45cm'])
            ->mapWithKeys(fn ($name) => [Str::slug($name) => Size::create(['name' => $name, 'slug' => Str::slug($name)])]);

        // Test prices in IRR — intentionally non-production placeholder values.
        $products = [
            ['name' => 'تی‌شرت', 'slug' => 't-shirt', 'category' => 'wearables', 'sizes' => ['S', 'M', 'L', 'XL', '2XL', '3XL'], 'colors' => ['white', 'black', 'red', 'blue'], 'base_price' => 250000, 'printing_price' => 80000],
            ['name' => 'هودی', 'slug' => 'hoodie', 'category' => 'wearables', 'sizes' => ['M', 'L', 'XL', '2XL'], 'colors' => ['black', 'gray', 'red'], 'base_price' => 650000, 'printing_price' => 100000],
            ['name' => 'ماگ سرامیکی', 'slug' => 'ceramic-mug', 'category' => 'drinkware', 'sizes' => ['11oz', '15oz'], 'colors' => ['white'], 'base_price' => 180000, 'printing_price' => 60000],
            ['name' => 'تامبلر', 'slug' => 'tumbler', 'category' => 'drinkware', 'sizes' => ['20oz'], 'colors' => ['white', 'black'], 'base_price' => 320000, 'printing_price' => 70000],
            ['name' => 'قاب موبایل', 'slug' => 'phone-case', 'category' => 'phone-cases', 'sizes' => ['iPhone 13', 'iPhone 14', 'iPhone 15', 'Galaxy A54', 'Galaxy S23'], 'colors' => ['white', 'black'], 'base_price' => 150000, 'printing_price' => 60000],
            ['name' => 'کیف دستی', 'slug' => 'tote-bag', 'category' => 'fabric', 'sizes' => ['37×42cm'], 'colors' => ['white'], 'base_price' => 190000, 'printing_price' => 70000],
            ['name' => 'کوسن', 'slug' => 'cushion-cover', 'category' => 'fabric', 'sizes' => ['40×40cm', '45×45cm'], 'colors' => ['white'], 'base_price' => 220000, 'printing_price' => 80000],
            ['name' => 'پازل', 'slug' => 'puzzle', 'category' => 'gifts', 'sizes' => ['22×18cm'], 'colors' => ['white'], 'base_price' => 160000, 'printing_price' => 50000],
        ];

        foreach ($products as $data) {
            $product = Product::create([
                'category_id' => $categories[$data['category']]->id,
                'name' => $data['name'], 'slug' => $data['slug'],
                'description' => 'محصول تستی چاپ سفارشی Etod Graphic.',
                'status' => 'published', 'is_customizable' => true,
                'base_price' => $data['base_price'], 'printing_price' => $data['printing_price'],
            ]);

            foreach ($data['sizes'] as $sizeName) {
                foreach ($data['colors'] as $colorSlug) {
                    $size = $sizes[Str::slug($sizeName)];
                    $color = $colors[$colorSlug];
                    $variant = ProductVariant::create([
                        'product_id' => $product->id, 'color_id' => $color->id, 'size_id' => $size->id,
                        'sku' => strtoupper($data['slug'].'-'.$colorSlug.'-'.Str::slug($sizeName)),
                        'stock' => 10, 'is_active' => true,
                    ]);
                    PrintTemplate::create([
                        'product_variant_id' => $variant->id, 'name' => 'قالب '.$data['name'],
                        'physical_width' => 20, 'physical_height' => 20, 'print_area_width' => 18,
                        'print_area_height' => 18, 'print_area_x' => 1, 'print_area_y' => 1,
                        'dpi' => 300, 'allowed_formats' => ['jpg', 'jpeg', 'png', 'webp'],
                    ]);
                }
            }
        }
    }
}
