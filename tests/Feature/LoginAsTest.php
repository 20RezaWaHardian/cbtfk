<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class LoginAsTest extends TestCase
{
    public function test_dashboard_uses_web_guard_before_checking_permission(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->id = 123;
        $user->username = 'ordinary';
        $user->shouldReceive('can')->once()->with('read dashboard')->andReturn(false);

        $this->actingAs($user, 'web')->get(route('dashboard'))->assertForbidden();
    }

    public function test_local_session_guard_and_login_routes(): void
    {
        $this->assertSame('session', config('auth.guards.web.driver'));
        $this->assertNull(config('auth.guards.local'));
        $this->assertFalse(app('router')->has('keycloak.login'));
        $this->assertSame(['POST'], app('router')->getRoutes()->getByName('users.login-as')->methods());
        $this->assertSame(['POST'], app('router')->getRoutes()->getByName('logout-as')->methods());
    }

    public function test_guest_cannot_impersonate(): void
    {
        $this->post(route('users.login-as', 'target'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_impersonate(): void
    {
        Gate::define('update konfigurasi/users', fn () => false);
        $user = new User(['id' => 123, 'username' => 'ordinary']);
        $this->actingAs($user, 'web')->postJson(route('users.login-as', 'target'))->assertForbidden();
    }

    public function test_nested_impersonation_is_rejected(): void
    {
        $user = new User(['id' => 123, 'username' => 'target']);
        $this->actingAs($user, 'web')->withSession(['impersonator_id' => 456])
            ->postJson(route('users.login-as', 'another'))->assertForbidden();
    }

    public function test_logout_as_requires_original_identity(): void
    {
        $user = new User(['id' => 123, 'username' => 'ordinary']);
        $this->actingAs($user, 'web')->postJson(route('logout-as'))->assertForbidden();
    }
    public function test_login_as_switches_identity_and_logout_restores_original(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        \Illuminate\Support\Facades\DB::purge('sqlite');
        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->string('username');
            $table->string('name');
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });
        foreach (glob(database_path('migrations/*create_permission_tables.php')) as $migration) {
            (require $migration)->up();
        }
        $admin = User::create(['username' => 'admin', 'name' => 'Admin']);
        $target = User::create(['username' => '199920932329333', 'name' => 'Target']);
        Gate::define('update konfigurasi/users', fn ($user) => $user->id === $admin->id);

        $this->actingAs($admin, 'web')->withSession([
            'userlogin' => ['username' => 'admin', 'nama_pelaku' => 'Admin'],
            'unrelated' => 'preserved',
        ])->postJson(route('users.login-as', $target->username))
            ->assertOk()->assertJson(['redirect' => route('dashboard')])
            ->assertSessionHas('impersonator_id', $admin->id)
            ->assertSessionHas('kamuflase', $target->username)
            ->assertSessionHas('userlogin.username', $target->username);
        $this->assertAuthenticatedAs($target, 'web');
        $this->assertFalse(Gate::allows('update konfigurasi/users'));
        $this->assertSame($target->id, session(auth()->guard('web')->getName()));

        $this->post(route('logout-as'))->assertRedirect(route('dashboard'))
            ->assertSessionMissing('kamuflase')->assertSessionMissing('impersonator_id')
            ->assertSessionMissing('impersonator_userlogin')
            ->assertSessionHas('userlogin.username', 'admin')
            ->assertSessionHas('unrelated', 'preserved');
        $this->assertAuthenticatedAs($admin, 'web');
        $this->assertSame($admin->id, session(auth()->guard('web')->getName()));
    }
}
