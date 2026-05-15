<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function testShareReturnsOnlyMinimalAuthenticatedUserPayload(): void
    {
        $user = User::factory()->create([
            'email' => 'minimal@example.com',
        ]);

        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new HandleInertiaRequests();
        $shared = $middleware->share($request);

        $this->assertSame([
            'id' => $user->id,
            'name' => $user->name,
            'email' => 'minimal@example.com',
        ], $shared['auth']['user']);
    }

    public function testShareReturnsNullForGuestUser(): void
    {
        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => null);

        $middleware = new HandleInertiaRequests();
        $shared = $middleware->share($request);

        $this->assertNull($shared['auth']['user']);
    }
}
