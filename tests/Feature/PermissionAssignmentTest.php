<?php

use App\Models\User;
use Silber\Bouncer\BouncerFacade;

beforeEach(function () {
    test()->be(User::factory()->create());

    // Set scope to be something arbitrary initially
    BouncerFacade::scope()->to(1);

    $this->user = User::factory()->create();
});

test('null scopes work as expected', function () {
    BouncerFacade::dontCache();

    // The user doesn't have the permission
    $this->assertFalse($this->user->can('view', User::class));

    // Give the user permission in the current scope (1)
    $this->user->allow('view', User::class);

    // They should have permission now
    $this->assertTrue($this->user->can('view', User::class));

    BouncerFacade::scope()->remove();

    // The user shouldn't have permission without the correct scope
    $this->assertFalse($this->user->can('view', User::class));
});

it('can change permissions correctly', function (array $data) {
    $this->postJson(route('users.permissions', $this->user), $data)
        ->assertOk();

    // Remove the scope now
    BouncerFacade::scope()->remove();
    $this->assertNull(BouncerFacade::scope()->get());

    // The permission has been correctly added
    $this->assertDatabaseHas('permissions', [
        'entity_id' => $this->user->id,
        'entity_type' => $this->user->getMorphClass(),
        'scope' => $data['scope'],
        'forbidden' => $data['allow'] ? 0 : 1,
    ]);


    // This should always be false because the permission was added with a scope
    // and now our scope is removed (null)
    ray()->showQueries(
        fn () => $this->assertFalse($this->user->can($data['ability'], $data['class'])),
    );

    // This should match the permission based on the scoping we're testing
    BouncerFacade::scope()->onceTo($data['scope'], function () use ($data) {
        ray()->showQueries(
            fn () => $this->assertEquals($data['allow'], $this->user->can($data['ability'], $data['class']))
        );
    });
})->with([
    "adding basic permission to separate scope" => function () {
        return [
            'scope' => 2,
            'class' => User::class,
            'ability' => 'view',
            'allow' => true,
        ];
    },
]);
