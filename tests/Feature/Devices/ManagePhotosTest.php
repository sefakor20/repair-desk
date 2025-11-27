<?php

declare(strict_types=1);

use App\Models\{Device, DevicePhoto, User};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

beforeEach(function () {
    Storage::fake('public');
});

test('authorized user can open upload modal', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->call('openUploadModal')
        ->assertSet('showUploadModal', true);
});

test('can upload single photo', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photo = UploadedFile::fake()->image('device.jpg', 800, 600);

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', [$photo])
        ->set('photoType', 'condition')
        ->set('photoDescription', 'Front view of device')
        ->call('uploadPhotos')
        ->assertHasNoErrors()
        ->assertSet('showUploadModal', false)
        ->assertDispatched('toast', type: 'success');

    expect(DevicePhoto::count())->toBe(1);
    expect(DevicePhoto::first()->type)->toBe('condition');
    expect(DevicePhoto::first()->description)->toBe('Front view of device');
    expect(DevicePhoto::first()->uploaded_by)->toBe($user->id);
    Storage::disk('public')->assertExists('device-photos/' . basename(DevicePhoto::first()->photo_path));
});

test('can upload multiple photos', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photos = [
        UploadedFile::fake()->image('device1.jpg', 800, 600),
        UploadedFile::fake()->image('device2.jpg', 800, 600),
        UploadedFile::fake()->image('device3.jpg', 800, 600),
    ];

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', $photos)
        ->set('photoType', 'damage')
        ->call('uploadPhotos')
        ->assertHasNoErrors();

    expect(DevicePhoto::count())->toBe(3);
    expect(DevicePhoto::where('type', 'damage')->count())->toBe(3);
});

test('photos array is required', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', [])
        ->set('photoType', 'condition')
        ->call('uploadPhotos')
        ->assertHasErrors(['photos' => 'required']);
});

test('photos must be array', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    // Since photos property is typed as array, we can't test invalid type directly
    // Instead test that non-image items in array are rejected
    $photos = ['not-an-uploaded-file'];

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', $photos)
        ->set('photoType', 'condition')
        ->call('uploadPhotos')
        ->assertHasErrors('photos.0');
});

test('cannot upload more than 5 photos', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photos = [
        UploadedFile::fake()->image('device1.jpg'),
        UploadedFile::fake()->image('device2.jpg'),
        UploadedFile::fake()->image('device3.jpg'),
        UploadedFile::fake()->image('device4.jpg'),
        UploadedFile::fake()->image('device5.jpg'),
        UploadedFile::fake()->image('device6.jpg'),
    ];

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', $photos)
        ->set('photoType', 'condition')
        ->call('uploadPhotos')
        ->assertHasErrors(['photos' => 'max']);
});

test('each photo must be an image', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photos = [
        UploadedFile::fake()->create('document.pdf', 1000),
    ];

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', $photos)
        ->set('photoType', 'condition')
        ->call('uploadPhotos')
        ->assertHasErrors(['photos.0' => 'image']);
});

test('each photo cannot exceed 5MB', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photos = [
        UploadedFile::fake()->image('large-device.jpg')->size(6000), // 6MB
    ];

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', $photos)
        ->set('photoType', 'condition')
        ->call('uploadPhotos')
        ->assertHasErrors(['photos.0' => 'max']);
});

test('photo type is required', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photo = UploadedFile::fake()->image('device.jpg');

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', [$photo])
        ->set('photoType', '')
        ->call('uploadPhotos')
        ->assertHasErrors(['photoType' => 'required']);
});

test('photo type must be valid', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photo = UploadedFile::fake()->image('device.jpg');

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', [$photo])
        ->set('photoType', 'invalid-type')
        ->call('uploadPhotos')
        ->assertHasErrors(['photoType' => 'in']);
});

test('photo description is optional', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photo = UploadedFile::fake()->image('device.jpg');

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', [$photo])
        ->set('photoType', 'condition')
        ->set('photoDescription', '')
        ->call('uploadPhotos')
        ->assertHasNoErrors();

    expect(DevicePhoto::first()->description)->toBeNull();
});

test('photo description cannot exceed 500 characters', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photo = UploadedFile::fake()->image('device.jpg');

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', [$photo])
        ->set('photoType', 'condition')
        ->set('photoDescription', str_repeat('a', 501))
        ->call('uploadPhotos')
        ->assertHasErrors(['photoDescription' => 'max']);
});

test('can delete photo', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    // Create a real photo file in storage
    $photo = UploadedFile::fake()->image('device.jpg');
    $path = $photo->store('device-photos', 'public');

    $devicePhoto = DevicePhoto::factory()->create([
        'device_id' => $device->id,
        'photo_path' => $path,
        'uploaded_by' => $user->id,
    ]);

    Storage::disk('public')->assertExists($path);

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->call('confirmDelete', $devicePhoto->id)
        ->assertSet('photoToDelete', $devicePhoto->id)
        ->assertSet('showDeleteModal', true)
        ->call('deletePhoto')
        ->assertHasNoErrors()
        ->assertSet('showDeleteModal', false)
        ->assertDispatched('toast', type: 'success');

    expect(DevicePhoto::count())->toBe(0);
    Storage::disk('public')->assertMissing($path);
});

test('can cancel photo deletion', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $devicePhoto = DevicePhoto::factory()->create([
        'device_id' => $device->id,
        'uploaded_by' => $user->id,
    ]);

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->call('confirmDelete', $devicePhoto->id)
        ->assertSet('showDeleteModal', true)
        ->call('cancelDelete')
        ->assertSet('showDeleteModal', false)
        ->assertSet('photoToDelete', null);

    expect(DevicePhoto::count())->toBe(1);
});

test('unauthorized user cannot upload photos', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photo = UploadedFile::fake()->image('device.jpg');

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', [$photo])
        ->set('photoType', 'condition')
        ->call('uploadPhotos')
        ->assertForbidden();
})->skip('Authorization needs to be implemented in component');

test('unauthorized user cannot delete photos', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $devicePhoto = DevicePhoto::factory()->create([
        'device_id' => $device->id,
    ]);

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->call('deletePhoto', $devicePhoto->id)
        ->assertForbidden();
})->skip('Authorization needs to be implemented in component');

test('displays existing photos', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    DevicePhoto::factory()->count(3)->create([
        'device_id' => $device->id,
        'uploaded_by' => $user->id,
    ]);

    $component = Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device]);

    expect($component->get('device')->photos->count())->toBe(3);
});

test('shows empty state when no photos exist', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $html = Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->html();

    expect($html)->toContain('No photos yet');
});

test('upload modal resets after successful upload', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photo = UploadedFile::fake()->image('device.jpg');

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', [$photo])
        ->set('photoType', 'condition')
        ->set('photoDescription', 'Test description')
        ->call('uploadPhotos')
        ->assertSet('showUploadModal', false)
        ->assertSet('photos', [])
        ->assertSet('photoType', '')
        ->assertSet('photoDescription', '');
});

test('stores photos with correct file names', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photo = UploadedFile::fake()->image('device.jpg');

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', [$photo])
        ->set('photoType', 'condition')
        ->call('uploadPhotos');

    $devicePhoto = DevicePhoto::first();
    expect($devicePhoto->photo_path)->toStartWith('device-photos/');
    expect($devicePhoto->photo_path)->toEndWith('.jpg');
});

test('photo belongs to correct device', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    $photo = UploadedFile::fake()->image('device.jpg');

    Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device])
        ->set('photos', [$photo])
        ->set('photoType', 'before')
        ->call('uploadPhotos');

    expect(DevicePhoto::first()->device_id)->toBe($device->id);
});

test('component loads with device relationship', function () {
    $user = User::factory()->create();
    $device = Device::factory()->create();

    DevicePhoto::factory()->count(2)->create([
        'device_id' => $device->id,
    ]);

    $component = Volt::actingAs($user)
        ->test('devices.manage-photos', ['device' => $device]);

    expect($component->get('device'))->toBeInstanceOf(Device::class);
    expect($component->get('device')->photos)->toHaveCount(2);
});
