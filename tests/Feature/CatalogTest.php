<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_and_upload_product_image(): void
    {
        Storage::fake('local');
        $category = Category::create(['name' => 'کاتالوگ', 'slug' => 'catalog', 'is_active' => true]);

        $response = $this->post(route('admin.products.store'), [
            'category_id' => $category->id, 'name' => 'ماگ تست', 'slug' => 'test-mug', 'description' => 'توضیح',
            'base_price' => 100000, 'printing_price' => 20000, 'status' => 'published', 'is_customizable' => true,
        ]);
        $product = Product::where('slug', 'test-mug')->firstOrFail();
        $response->assertRedirect(route('admin.products.edit', $product));

        $this->post(route('admin.products.images.store', $product), ['image' => UploadedFile::fake()->image('mug.png', 800, 800), 'alt_text' => 'ماگ تست', 'is_primary' => 1])->assertRedirect();
        $this->assertDatabaseHas('product_images', ['product_id' => $product->id, 'is_primary' => true]);
        Storage::disk('local')->assertExists($product->fresh()->images->first()->path);
    }

    public function test_published_product_page_contains_catalog_image_route(): void
    {
        Storage::fake('local');
        $category = Category::create(['name' => 'نمایش', 'slug' => 'display', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'محصول عکس‌دار', 'slug' => 'image-product', 'status' => 'published']);
        $product->images()->create(['disk' => 'local', 'path' => 'product-images/test.png', 'alt_text' => 'تصویر محصول', 'is_primary' => true]);

        Storage::disk('local')->put('product-images/test.png', 'fake-image');
        $this->get(route('products.show', $product))->assertOk()->assertSee(route('products.images.show', $product->images->first()), false);
    }
}
