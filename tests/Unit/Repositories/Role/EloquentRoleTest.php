<?php

namespace Tests\Unit\Repositories\Role;

use Event;
use Facades\Tests\Setup\RoleFactory;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Events\Role\Created;
use App\Models\Role;
use App\Repositories\Role\EloquentRole;

class EloquentRoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var EloquentRole
     */
    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(EloquentRole::class);
    }

    public function test_all()
    {
        $roles = Role::factory()->times(4)->create();

        $this->assertEquals(
            $roles->toArray(),
            $this->repo->all()->toArray()
        );
    }

    public function test_get_all_with_users_count()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::create();
        $roleC = RoleFactory::create();

        UserFactory::role($roleA)->create();
        UserFactory::role($roleA)->create();
        UserFactory::role($roleB)->create();

        $roleA->users_count = 2;
        $roleB->users_count = 1;
        $roleC->users_count = 0;

        $this->assertEquals(
            [$roleA->toArray(), $roleB->toArray(), $roleC->toArray()],
            $this->repo->getAllWithUsersCount()->toArray()
        );
    }

    public function test_create()
    {
        Event::fake([
            Created::class,
        ]);

        $data = ['name' => 'foo', 'display_name' => 'Foo'];
        $role = $this->repo->create($data);

        $this->assertDatabaseHas('roles', $data + ['id' => $role->id]);

        Event::assertDispatched(Created::class);
    }

    public function test_update()
    {
        Event::fake([
            \App\Events\Role\Updated::class,
        ]);

        $role = Role::factory()->create();

        $data = ['name' => 'foo', 'display_name' => 'Foo'];

        $this->repo->update($role->id, $data);

        $this->assertDatabaseHas('roles', $data + ['id' => $role->id]);

        Event::assertDispatched(\App\Events\Role\Updated::class);
    }

    public function test_delete_role()
    {
        Event::fake([
            \App\Events\Role\Deleted::class,
        ]);

        $role = Role::factory()->create();

        $this->repo->delete($role->id);

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);

        Event::assertDispatched(\App\Events\Role\Deleted::class);
    }

    public function test_updatePermissions()
    {
        $role = Role::factory()->create();
        $permissions = \App\Models\Permission::factory()->times(2)->create();

        $this->repo->updatePermissions($role->id, $permissions->pluck('id')->toArray());

        $this->assertDatabaseHas('permission_role', ['role_id' => $role->id, 'permission_id' => $permissions[0]->id]);
        $this->assertDatabaseHas('permission_role', ['role_id' => $role->id, 'permission_id' => $permissions[1]->id]);
    }

    public function test_lists()
    {
        $roles = Role::factory()->times(4)->create();
        $roles = $roles->pluck('display_name', 'id');

        $this->assertEquals($roles->toArray(), $this->repo->lists()->toArray());
    }
}
