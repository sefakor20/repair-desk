<?php

declare(strict_types=1);

use App\Enums\DeviceCategory;
use App\Livewire\Admin\Faults\Form as FaultsForm;
use App\Livewire\Admin\Faults\Index as FaultsIndex;
use App\Models\CommonFault;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->admin()->create();

    actingAs($this->user);
});

test('authorized user can view faults index', function (): void {
    Livewire::test(FaultsIndex::class)
        ->assertOk();
});

test('faults index displays faults list', function (): void {
    $faults = CommonFault::factory()->count(3)->create([
        'device_category' => DeviceCategory::Smartphone,
    ]);

    Livewire::test(FaultsIndex::class)
        ->assertSee($faults->first()->name)
        ->assertSee($faults->last()->name);
});

test('can search faults by name', function (): void {
    CommonFault::factory()->create(['name' => 'Cracked Screen']);
    CommonFault::factory()->create(['name' => 'Battery Drain']);

    Livewire::test(FaultsIndex::class)
        ->set('search', 'Cracked')
        ->assertSee('Cracked Screen')
        ->assertDontSee('Battery Drain');
});

test('can filter faults by category', function (): void {
    CommonFault::factory()->create([
        'name' => 'Broken Keyboard',
        'device_category' => DeviceCategory::Laptop,
    ]);

    CommonFault::factory()->create([
        'name' => 'Cracked Screen',
        'device_category' => DeviceCategory::Smartphone,
    ]);

    Livewire::test(FaultsIndex::class)
        ->set('categoryFilter', DeviceCategory::Laptop->value)
        ->assertSee('Broken Keyboard')
        ->assertDontSee('Cracked Screen');
});

test('can filter universal faults', function (): void {
    CommonFault::factory()->create([
        'name' => 'Water Damage',
        'device_category' => null, // Universal
    ]);

    CommonFault::factory()->create([
        'name' => 'Cracked Screen',
        'device_category' => DeviceCategory::Smartphone,
    ]);

    Livewire::test(FaultsIndex::class)
        ->set('categoryFilter', 'universal')
        ->assertSee('Water Damage')
        ->assertDontSee('Cracked Screen');
});

test('can toggle fault status', function (): void {
    $fault = CommonFault::factory()->create(['is_active' => true]);

    Livewire::test(FaultsIndex::class)
        ->call('toggleStatus', $fault->id);

    expect($fault->fresh()->is_active)->toBeFalse();
});

test('can delete fault', function (): void {
    $fault = CommonFault::factory()->create();

    Livewire::test(FaultsIndex::class)
        ->call('delete', $fault->id);

    expect(CommonFault::find($fault->id))->toBeNull();
});

test('can create new fault', function (): void {
    Livewire::test(FaultsForm::class)
        ->set('name', 'Cracked Screen')
        ->set('description', 'Display has visible cracks')
        ->set('device_category', DeviceCategory::Smartphone->value)
        ->set('sort_order', 10)
        ->set('is_active', true)
        ->call('save')
        ->assertRedirect(route('admin.faults.index'));

    $fault = CommonFault::where('name', 'Cracked Screen')->first();

    expect($fault)->not->toBeNull()
        ->and($fault->device_category)->toBe(DeviceCategory::Smartphone)
        ->and($fault->description)->toBe('Display has visible cracks')
        ->and($fault->sort_order)->toBe(10)
        ->and($fault->is_active)->toBeTrue();
});

test('can create universal fault', function (): void {
    Livewire::test(FaultsForm::class)
        ->set('name', 'Water Damage')
        ->set('device_category', 'universal')
        ->set('sort_order', 10)
        ->call('save')
        ->assertRedirect(route('admin.faults.index'));

    $fault = CommonFault::where('name', 'Water Damage')->first();

    expect($fault)->not->toBeNull()
        ->and($fault->device_category)->toBeNull();
});

test('can edit existing fault', function (): void {
    $fault = CommonFault::factory()->create([
        'name' => 'Old Fault',
        'device_category' => DeviceCategory::Smartphone,
    ]);

    Livewire::test(FaultsForm::class, ['fault' => $fault])
        ->set('name', 'Updated Fault')
        ->call('save')
        ->assertRedirect(route('admin.faults.index'));

    expect($fault->fresh()->name)->toBe('Updated Fault');
});

test('fault name is required', function (): void {
    Livewire::test(FaultsForm::class)
        ->set('name', '')
        ->set('device_category', DeviceCategory::Smartphone->value)
        ->call('save')
        ->assertHasErrors(['name']);
});

test('faults are ordered by sort_order', function (): void {
    CommonFault::factory()->create(['name' => 'Third', 'sort_order' => 30]);
    CommonFault::factory()->create(['name' => 'First', 'sort_order' => 10]);
    CommonFault::factory()->create(['name' => 'Second', 'sort_order' => 20]);

    $faults = CommonFault::query()->ordered()->get();

    expect($faults->first()->name)->toBe('First')
        ->and($faults->last()->name)->toBe('Third');
});

test('unauthorized user cannot access faults management', function (): void {
    $unauthorizedUser = User::factory()->create();

    actingAs($unauthorizedUser);

    Livewire::test(FaultsIndex::class)
        ->assertForbidden();
});
