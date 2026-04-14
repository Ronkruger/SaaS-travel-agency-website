<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Authentication Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function unauthenticated_users_cannot_access_bookings(): void
    {
        $response = $this->get('/bookings');
        
        $response->assertRedirect('/login');
    }

    /** @test */
    public function unauthenticated_users_cannot_access_checkout(): void
    {
        $response = $this->get('/checkout/1');
        
        $response->assertRedirect('/login');
    }

    /** @test */
    public function unauthenticated_users_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin/dashboard');
        
        $response->assertRedirect('/admin/auth/login');
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization Tests (IDOR Prevention)
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function users_cannot_access_other_users_bookings(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tour = Tour::factory()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $user1->id,
            'tour_id' => $tour->id,
        ]);

        $this->actingAs($user2);
        
        $response = $this->get("/bookings/{$booking->id}");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function users_can_access_their_own_bookings(): void
    {
        $user = User::factory()->create();
        $tour = Tour::factory()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
        ]);

        $this->actingAs($user);
        
        $response = $this->get("/bookings/{$booking->id}");
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_any_users_booking(): void
    {
        $admin = AdminUser::factory()->superAdmin()->create();
        $user = User::factory()->create();
        $tour = Tour::factory()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
        ]);

        $this->actingAs($admin, 'admin');
        
        $response = $this->get("/admin/bookings/{$booking->id}");
        
        $response->assertStatus(200);
    }

    /** @test */
    public function users_cannot_cancel_other_users_bookings(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tour = Tour::factory()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $user1->id,
            'tour_id' => $tour->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user2);
        
        $response = $this->post("/bookings/{$booking->id}/cancel");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function users_cannot_access_other_users_checkout(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tour = Tour::factory()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $user1->id,
            'tour_id' => $tour->id,
        ]);

        $this->actingAs($user2);
        
        $response = $this->get("/checkout/{$booking->id}");
        
        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Input Validation Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function non_existent_booking_returns_404(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->get('/bookings/999999');
        
        $response->assertStatus(404);
    }

    /** @test */
    public function invalid_booking_id_format_returns_error(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Test with negative ID
        $response = $this->get('/bookings/-1');
        $response->assertStatus(404);
        
        // Test with string
        $response = $this->get('/bookings/abc');
        $response->assertStatus(404);
        
        // Test with SQL injection attempt
        $response = $this->get('/bookings/1%27%20OR%201=1');
        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Access Control Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function non_admin_users_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $this->actingAs($user);
        
        $response = $this->get('/admin/dashboard');
        
        // Web-guard users are not authenticated via the admin guard — expect a login redirect
        $response->assertRedirect('/admin/auth/login');
    }

    /** @test */
    public function non_admin_users_cannot_access_user_management(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $this->actingAs($user);
        
        $response = $this->get('/admin/users');
        
        $response->assertRedirect('/admin/auth/login');
    }

    /** @test */
    public function admin_can_view_user_list(): void
    {
        $admin = AdminUser::factory()->superAdmin()->create();
        
        $this->actingAs($admin, 'admin');
        
        $response = $this->get('/admin/users');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $this->actingAs($admin);
        
        $response = $this->delete("/admin/users/{$admin->id}");
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Verify admin still exists
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function login_endpoint_is_rate_limited(): void
    {
        // Make 6 requests (limit is 5 per minute)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }
        
        // The 6th request should be rate limited
        $response->assertStatus(429);
    }

    /*
    |--------------------------------------------------------------------------
    | Data Sanitization Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function user_data_does_not_expose_password_hash(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        
        $this->actingAs($admin);
        
        $response = $this->get("/admin/users/{$user->id}");
        
        $response->assertDontSee($user->password);
        $response->assertDontSee('remember_token');
    }

    /*
    |--------------------------------------------------------------------------
    | Review Authorization Tests
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function users_cannot_delete_other_users_reviews(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tour = Tour::factory()->create();
        
        $review = \App\Models\Review::factory()->create([
            'user_id' => $user1->id,
            'tour_id' => $tour->id,
        ]);

        $this->actingAs($user2);
        
        $response = $this->delete("/reviews/{$review->id}");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function users_can_delete_their_own_reviews(): void
    {
        $user = User::factory()->create();
        $tour = Tour::factory()->create();
        
        $review = \App\Models\Review::factory()->create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
        ]);

        $this->actingAs($user);
        
        $response = $this->delete("/reviews/{$review->id}");
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
