<?php

declare(strict_types=1);

use App\Enums\DeviceCategory;
use App\Livewire\Admin\Models\Form as ModelsForm;
use App\Livewire\Admin\Models\Index as ModelsIndex;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->admin()->create();

    actingAs($this->user);

    $this->brand = DeviceBrand::factory()->create([
        'category' => DeviceCategory::Smartphone,
    ]);
});

test('authorized user can view models index', function (): void {
    Livewire::test(ModelsIndex::class)
        ->assertOk();
});

test('models index displays models list', function (): void {
    $models = DeviceModel::factory()->count(3)->create([
        'brand_id' => $this->brand->id,
        'category' => DeviceCategory::Smartphone,
    ]);

    Livewire::test(ModelsIndex::class)
        ->assertSee($models->first()->name)
        ->assertSee($models->last()->name);
});

test('can search models by name', function (): void {
    DeviceModel::factory()->create([
        'name' => 'iPhone 15',
        'brand_id' => $this->brand->id,
        'category' => DeviceCategory::Smartphone,
    ]);

    DeviceModel::factory()->create([
        'name' => 'Galaxy S24',
        'brand_id' => $this->brand->id,
        'category' => DeviceCategory::Smartphone,
    ]);

    Livewire::test(ModelsIndex::class)
        ->set('search', 'iPhone')
        ->assertSee('iPhone 15')
        ->assertDontSee('Galaxy S24');
});

test('can filter models by category', function (): void {
    $laptopBrand = DeviceBrand::factory()->create([
        'category' => DeviceCategory::Laptop,
    ]);

    DeviceModel::factory()->create([
        'name' => 'iPhone 15',
        'brand_id' => $this->brand->id,
        'category' => DeviceCategory::Smartphone,
    ]);

    DeviceModel::factory()->create([
        'name' => 'MacBook Pro',
        'brand_id' => $laptopBrand->id,
        'category' => DeviceCategory::Laptop,
    ]);

    Livewire::test(ModelsIndex::class)
        ->set('categoryFilter', DeviceCategory::Smartphone->value)
        ->assertSee('iPhone 15')
        ->assertDontSee('MacBook Pro');
});

test('can toggle model status', function (): void {
    $model = DeviceModel::factory()->create([
        'brand_id' => $this->brand->id,
        'is_active' => true,
    ]);

    Livewire::test(ModelsIndex::class)
        ->call('toggleStatus', $model->id);

    expect($model->fresh()->is_active)->toBeFalse();
});

test('can delete model', function (): void {
    $model = DeviceModel::factory()->create(['brand_id' => $this->brand->id]);

    Livewire::test(ModelsIndex::class)
        ->call('delete', $model->id);

    expect(DeviceModel::find($model->id))->toBeNull();
});

test('can create new model', function (): void {
    Livewire::test(ModelsForm::class)
        ->set('name', 'iPhone 15 Pro')
        ->set('category', DeviceCategory::Smartphone->value)
        ->set('brand_id', $this->brand->id)
        ->set('specifications', ['storage' => '256GB', 'ram' => '8GB'])
        ->set('is_active', true)
        ->call('save')
        ->assertRedirect(route('admin.models.index'));

    $model = DeviceModel::where('name', 'iPhone 15 Pro')->first();

    expect($model)->not->toBeNull()
        ->and($model->category)->toBe(DeviceCategory::Smartphone)
        ->and($model->brand_id)->toBe($this->brand->id)
        ->and($model->specifications)->toBe(['storage' => '256GB', 'ram' => '8GB'])
        ->and($model->is_active)->toBeTrue();
});

test('can edit existing model', function (): void {
    $model = DeviceModel::factory()->create([
        'name' => 'Old Name',
        'brand_id' => $this->brand->id,
        'category' => DeviceCategory::Smartphone,
    ]);

    Livewire::test(ModelsForm::class, ['model' => $model])
        ->set('name', 'New Name')
        ->call('save')
        ->assertRedirect(route('admin.models.index'));

    expect($model->fresh()->name)->toBe('New Name');
});

test('model name is required', function (): void {
    Livewire::test(ModelsForm::class)
        ->set('name', '')
        ->set('category', DeviceCategory::Smartphone->value)
        ->set('brand_id', $this->brand->id)
        ->call('save')
        ->assertHasErrors(['name']);
});

test('model brand is required', function (): void {
    Livewire::test(ModelsForm::class)
        ->set('name', 'iPhone 15')
        ->set('category', DeviceCategory::Smartphone->value)
        ->set('brand_id', null)
        ->call('save')
        ->assertHasErrors(['brand_id']);
});

test('brand selection resets when category changes', function (): void {
    Livewire::test(ModelsForm::class)
        ->set('category', DeviceCategory::Smartphone->value)
        ->set('brand_id', $this->brand->id)
        ->set('category', DeviceCategory::Laptop->value)
        ->assertSet('brand_id', null);
});

test('unauthorized user cannot access models management', function (): void {
    $unauthorizedUser = User::factory()->create();

    actingAs($unauthorizedUser);

    Livewire::test(ModelsIndex::class)
        ->assertForbidden();
});
