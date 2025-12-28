<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->admin()->create();
});

test('user can view contacts index page', function (): void {
    $contacts = Contact::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get(route('admin.contacts.index'))
        ->assertStatus(200);
});

test('contacts index shows contact list', function (): void {
    $contact = Contact::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '+233241234567',
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Admin\Contacts\Index::class)
        ->assertSee('John Doe')
        ->assertSee('john@example.com')
        ->assertSee('+233241234567');
});

test('user can create a new contact', function (): void {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Admin\Contacts\Create::class)
        ->set('first_name', 'Jane')
        ->set('last_name', 'Smith')
        ->set('email', 'jane@example.com')
        ->set('phone', '+233501234567')
        ->set('company', 'Test Company')
        ->set('position', 'Manager')
        ->call('save')
        ->assertRedirect(route('admin.contacts.index'));

    expect(Contact::where('first_name', 'Jane')->where('last_name', 'Smith')->exists())
        ->toBeTrue();
});

test('user can edit an existing contact', function (): void {
    $contact = Contact::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '+233241234567',
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\Admin\Contacts\Edit::class, ['contact' => $contact])
        ->set('first_name', 'Johnny')
        ->set('email', 'johnny@example.com')
        ->call('save')
        ->assertRedirect(route('admin.contacts.index'));

    $contact->refresh();
    expect($contact->first_name)->toBe('Johnny');
    expect($contact->email)->toBe('johnny@example.com');
});

test('contacts appear in sms campaign creation', function (): void {
    $contact = Contact::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+233241234567',
        'company' => null,
        'position' => null,
        'is_active' => true,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(\App\Livewire\Admin\CreateSmsCampaign::class);

    $availableContacts = $component->get('availableContacts');

    expect(collect($availableContacts))
        ->toHaveCount(1)
        ->and(collect($availableContacts)->first()['name'])
        ->toBe('John Doe');
});
