<?php

declare(strict_types=1);

use App\Models\SmsTemplate;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('admin can view sms templates index', function () {
    $admin = User::factory()->create();
    $admin->role = \App\Enums\UserRole::Admin;
    $admin->save();

    actingAs($admin);

    $template = SmsTemplate::create([
        'name' => 'Test Template',
        'key' => 'test_template',
        'message' => 'Hello {{customer_name}}!',
        'is_active' => true,
    ]);

    Livewire::test(\App\Livewire\Admin\SmsTemplates\Index::class)
        ->assertSee('Test Template')
        ->assertSee('test_template')
        ->assertStatus(200);
});

test('admin can create sms template', function () {
    $admin = User::factory()->create();
    $admin->role = \App\Enums\UserRole::Admin;
    $admin->save();

    actingAs($admin);

    Livewire::test(\App\Livewire\Admin\SmsTemplates\Form::class)
        ->set('name', 'New Template')
        ->set('key', 'new_template')
        ->set('message', 'Hi {{name}}, welcome!')
        ->set('description', 'Test description')
        ->set('is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    expect(SmsTemplate::where('key', 'new_template')->exists())->toBeTrue();
});

test('admin can edit sms template', function () {
    $admin = User::factory()->create();
    $admin->role = \App\Enums\UserRole::Admin;
    $admin->save();

    actingAs($admin);

    $template = SmsTemplate::create([
        'name' => 'Old Name',
        'key' => 'test_template',
        'message' => 'Old message',
        'is_active' => true,
    ]);

    Livewire::test(\App\Livewire\Admin\SmsTemplates\Form::class, ['templateId' => $template->id])
        ->set('name', 'Updated Name')
        ->set('message', 'Updated message {{variable}}')
        ->call('save')
        ->assertHasNoErrors();

    expect($template->fresh()->name)->toBe('Updated Name')
        ->and($template->fresh()->message)->toBe('Updated message {{variable}}');
});

test('admin can toggle template status', function () {
    $admin = User::factory()->create();
    $admin->role = \App\Enums\UserRole::Admin;
    $admin->save();

    actingAs($admin);

    $template = SmsTemplate::create([
        'name' => 'Test Template',
        'key' => 'test_template',
        'message' => 'Test',
        'is_active' => true,
    ]);

    Livewire::test(\App\Livewire\Admin\SmsTemplates\Index::class)
        ->call('toggleStatus', $template->id)
        ->assertDispatched('template-updated');

    expect($template->fresh()->is_active)->toBeFalse();
});

test('admin can delete template', function () {
    $admin = User::factory()->create();
    $admin->role = \App\Enums\UserRole::Admin;
    $admin->save();

    actingAs($admin);

    $template = SmsTemplate::create([
        'name' => 'Test Template',
        'key' => 'test_template',
        'message' => 'Test',
        'is_active' => true,
    ]);

    Livewire::test(\App\Livewire\Admin\SmsTemplates\Index::class)
        ->call('delete', $template->id)
        ->assertDispatched('template-deleted');

    expect(SmsTemplate::where('id', $template->id)->exists())->toBeFalse();
});

test('template renders variables correctly', function () {
    $template = SmsTemplate::create([
        'name' => 'Test Template',
        'key' => 'test_template',
        'message' => 'Hello {{customer_name}}, your {{ticket_number}} is ready!',
        'is_active' => true,
    ]);

    $rendered = $template->render([
        'customer_name' => 'John Doe',
        'ticket_number' => 'T-12345',
    ]);

    expect($rendered)->toBe('Hello John Doe, your T-12345 is ready!');
});

test('template extracts variables correctly', function () {
    $template = SmsTemplate::create([
        'name' => 'Test Template',
        'key' => 'test_template',
        'message' => 'Hello {{customer_name}}, your {{ticket_number}} is ready!',
        'is_active' => true,
    ]);

    $variables = $template->extractVariables();

    expect($variables)->toBe(['customer_name', 'ticket_number']);
});

test('template key must be unique', function () {
    $admin = User::factory()->create();
    $admin->role = \App\Enums\UserRole::Admin;
    $admin->save();

    actingAs($admin);

    SmsTemplate::create([
        'name' => 'First Template',
        'key' => 'duplicate_key',
        'message' => 'Test',
        'is_active' => true,
    ]);

    Livewire::test(\App\Livewire\Admin\SmsTemplates\Form::class)
        ->set('name', 'Second Template')
        ->set('key', 'duplicate_key')
        ->set('message', 'Test message')
        ->call('save')
        ->assertHasErrors(['key']);
});

test('template requires all mandatory fields', function () {
    $admin = User::factory()->create();
    $admin->role = \App\Enums\UserRole::Admin;
    $admin->save();

    actingAs($admin);

    Livewire::test(\App\Livewire\Admin\SmsTemplates\Form::class)
        ->set('name', '')
        ->set('key', '')
        ->set('message', '')
        ->call('save')
        ->assertHasErrors(['name', 'key', 'message']);
});

test('non-admin cannot access sms templates', function () {
    $user = User::factory()->create();
    $user->role = \App\Enums\UserRole::Technician;
    $user->save();

    actingAs($user);

    Livewire::test(\App\Livewire\Admin\SmsTemplates\Index::class)
        ->assertForbidden();
});
