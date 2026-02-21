<?php

declare(strict_types=1);

use App\Enums\DeviceCategory;
use App\Livewire\Admin\Brands\Form as BrandsForm;
use App\Livewire\Admin\Brands\Index as BrandsIndex;
use App\Models\DeviceBrand;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    Storage::fake('public');

    $this->user = User::factory()->admin()->create();

    actingAs($this->user);
});

test('authorized user can view brands index', function (): void {
    Livewire::test(BrandsIndex::class)
        ->assertOk();
});

test('brands index displays brands list', function (): void {
    $brands = DeviceBrand::factory()->count(3)->create([
        'category' => DeviceCategory::Smartphone,
    ]);

    Livewire::test(BrandsIndex::class)
        ->assertSee($brands->first()->name)
        ->assertSee($brands->last()->name);
});

test('can search brands by name', function (): void {
    DeviceBrand::factory()->create(['name' => 'Apple']);
    DeviceBrand::factory()->create(['name' => 'Samsung']);

    Livewire::test(BrandsIndex::class)
        ->set('search', 'Apple')
        ->assertSee('Apple')
        ->assertDontSee('Samsung');
});

test('can filter brands by category', function (): void {
    DeviceBrand::factory()->create([
        'name' => 'Apple',
        'category' => DeviceCategory::Smartphone,
    ]);

    DeviceBrand::factory()->create([
        'name' => 'Dell',
        'category' => DeviceCategory::Laptop,
    ]);

    Livewire::test(BrandsIndex::class)
        ->set('categoryFilter', DeviceCategory::Smartphone->value)
        ->assertSee('Apple')
        ->assertDontSee('Dell');
});

test('can toggle brand status', function (): void {
    $brand = DeviceBrand::factory()->create(['is_active' => true]);

    Livewire::test(BrandsIndex::class)
        ->call('toggleStatus', $brand->id);

    expect($brand->fresh()->is_active)->toBeFalse();
});

test('can delete brand', function (): void {
    $brand = DeviceBrand::factory()->create();

    Livewire::test(BrandsIndex::class)
        ->call('delete', $brand->id);

    expect(DeviceBrand::find($brand->id))->toBeNull();
});

test('can create new brand', function (): void {
    $logo = UploadedFile::fake()->image('logo.png');

    Livewire::test(BrandsForm::class)
        ->set('name', 'Apple')
        ->set('category', DeviceCategory::Smartphone->value)
        ->set('logo', $logo)
        ->set('is_active', true)
        ->call('save')
        ->assertRedirect(route('admin.brands.index'));

    $brand = DeviceBrand::where('name', 'Apple')->first();

    expect($brand)->not->toBeNull()
        ->and($brand->category)->toBe(DeviceCategory::Smartphone)
        ->and($brand->is_active)->toBeTrue()
        ->and($brand->logo_path)->not->toBeNull();

    Storage::disk('public')->assertExists($brand->logo_path);
});

test('can edit existing brand', function (): void {
    $brand = DeviceBrand::factory()->create([
        'name' => 'Old Name',
        'category' => DeviceCategory::Smartphone,
    ]);

    Livewire::test(BrandsForm::class, ['brand' => $brand])
        ->set('name', 'New Name')
        ->call('save')
        ->assertRedirect(route('admin.brands.index'));

    expect($brand->fresh()->name)->toBe('New Name');
});

test('brand name is required', function (): void {
    Livewire::test(BrandsForm::class)
        ->set('name', '')
        ->set('category', DeviceCategory::Smartphone->value)
        ->call('save')
        ->assertHasErrors(['name']);
});

test('brand category is required', function (): void {
    Livewire::test(BrandsForm::class)
        ->set('name', 'Apple')
        ->set('category', '')
        ->call('save')
        ->assertHasErrors(['category']);
});

test('unauthorized user cannot access brands management', function (): void {
    $unauthorizedUser = User::factory()->create();

    actingAs($unauthorizedUser);

    Livewire::test(BrandsIndex::class)
        ->assertForbidden();
});
