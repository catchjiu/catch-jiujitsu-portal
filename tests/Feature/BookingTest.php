<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\MembershipPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test member can view schedule.
     */
    public function test_member_can_view_schedule(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/schedule');

        $response->assertStatus(200);
    }

    /**
     * Test member with active membership can book a class.
     */
    public function test_member_with_active_membership_can_book_class(): void
    {
        $user = User::factory()->withActiveMembership()->create();
        $class = ClassSession::factory()->create();

        $response = $this->actingAs($user)->post('/book', [
            'class_id' => $class->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);
    }

    /**
     * Test member without active membership cannot book a class.
     */
    public function test_member_without_active_membership_cannot_book(): void
    {
        $user = User::factory()->create([
            'membership_status' => 'none',
        ]);
        $class = ClassSession::factory()->create();

        $response = $this->actingAs($user)->post('/book', [
            'class_id' => $class->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('bookings', [
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);
    }

    /**
     * Test member with expired membership cannot book a class.
     */
    public function test_member_with_expired_membership_cannot_book(): void
    {
        $user = User::factory()->withExpiredMembership()->create();
        $class = ClassSession::factory()->create();

        $response = $this->actingAs($user)->post('/book', [
            'class_id' => $class->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('bookings', [
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);
    }

    /**
     * Test gratis member can always book.
     */
    public function test_gratis_member_can_always_book(): void
    {
        $user = User::factory()->gratis()->create();
        $class = ClassSession::factory()->create();

        $response = $this->actingAs($user)->post('/book', [
            'class_id' => $class->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);
    }

    /**
     * Test member cannot double book the same class.
     */
    public function test_member_cannot_double_book_same_class(): void
    {
        $user = User::factory()->withActiveMembership()->create();
        $class = ClassSession::factory()->create();

        // First booking
        Booking::factory()->forUser($user)->forClass($class)->create();

        // Try to book again
        $response = $this->actingAs($user)->post('/book', [
            'class_id' => $class->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Should still only have one booking
        $this->assertEquals(1, Booking::where('user_id', $user->id)->where('class_id', $class->id)->count());
    }

    /**
     * Test member cannot book a full class.
     */
    public function test_member_cannot_book_full_class(): void
    {
        $user = User::factory()->withActiveMembership()->create();
        $class = ClassSession::factory()->withCapacity(2)->create();

        // Fill the class
        Booking::factory()->count(2)->forClass($class)->create();

        $response = $this->actingAs($user)->post('/book', [
            'class_id' => $class->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('bookings', [
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);
    }

    /**
     * Test member can cancel a booking.
     */
    public function test_member_can_cancel_booking(): void
    {
        $user = User::factory()->withActiveMembership()->create();
        $class = ClassSession::factory()->create();

        Booking::factory()->forUser($user)->forClass($class)->create();

        $response = $this->actingAs($user)->delete('/book/' . $class->id);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('bookings', [
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);
    }

    /**
     * Test member cannot cancel non-existent booking.
     */
    public function test_member_cannot_cancel_nonexistent_booking(): void
    {
        $user = User::factory()->withActiveMembership()->create();
        $class = ClassSession::factory()->create();

        $response = $this->actingAs($user)->delete('/book/' . $class->id);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test classes remaining decrements on booking (class-based package).
     */
    public function test_classes_remaining_decrements_on_booking(): void
    {
        $package = MembershipPackage::factory()->classBased(10)->create();
        $user = User::factory()->create([
            'membership_status' => 'active',
            'membership_package_id' => $package->id,
            'classes_remaining' => 5,
        ]);
        $class = ClassSession::factory()->create();

        $this->actingAs($user)->post('/book', [
            'class_id' => $class->id,
        ]);

        $user->refresh();
        $this->assertEquals(4, $user->classes_remaining);
    }

    /**
     * Test classes remaining increments on cancel (class-based package).
     */
    public function test_classes_remaining_increments_on_cancel(): void
    {
        $package = MembershipPackage::factory()->classBased(10)->create();
        $user = User::factory()->create([
            'membership_status' => 'active',
            'membership_package_id' => $package->id,
            'classes_remaining' => 4,
        ]);
        $class = ClassSession::factory()->create();
        Booking::factory()->forUser($user)->forClass($class)->create();

        $this->actingAs($user)->delete('/book/' . $class->id);

        $user->refresh();
        $this->assertEquals(5, $user->classes_remaining);
    }

    /**
     * Test member with zero classes remaining cannot book.
     */
    public function test_member_with_zero_classes_remaining_cannot_book(): void
    {
        $package = MembershipPackage::factory()->classBased(10)->create();
        $user = User::factory()->create([
            'membership_status' => 'active',
            'membership_package_id' => $package->id,
            'classes_remaining' => 0,
        ]);
        $class = ClassSession::factory()->create();

        $response = $this->actingAs($user)->post('/book', [
            'class_id' => $class->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('bookings', [
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);
    }

    /**
     * Test booking requires valid class_id.
     */
    public function test_booking_requires_valid_class_id(): void
    {
        $user = User::factory()->withActiveMembership()->create();

        $response = $this->actingAs($user)->post('/book', [
            'class_id' => 99999,
        ]);

        $response->assertSessionHasErrors('class_id');
    }

    /**
     * Test schedule filters by age group.
     */
    public function test_schedule_filters_by_age_group(): void
    {
        $user = User::factory()->create(['age_group' => 'Adults']);
        
        $adultClass = ClassSession::factory()->forAdults()->today()->create(['title' => 'Adult Class']);
        $kidsClass = ClassSession::factory()->forKids()->today()->create(['title' => 'Kids Class']);
        $allClass = ClassSession::factory()->forAllAges()->today()->create(['title' => 'All Ages Class']);

        // Adults filter should show adult and all ages classes
        $response = $this->actingAs($user)->get('/schedule?filter=Adults&date=' . now()->format('Y-m-d'));
        $response->assertStatus(200);
        $response->assertSee('Adult Class');
        $response->assertSee('All Ages Class');
        $response->assertDontSee('Kids Class');

        // Kids filter should show kids and all ages classes
        $response = $this->actingAs($user)->get('/schedule?filter=Kids&date=' . now()->format('Y-m-d'));
        $response->assertStatus(200);
        $response->assertSee('Kids Class');
        $response->assertSee('All Ages Class');
        $response->assertDontSee('Adult Class');
    }

    /**
     * Test coach can view class attendance.
     */
    public function test_coach_can_view_class_attendance(): void
    {
        $coach = User::factory()->coach()->create();
        $class = ClassSession::factory()->create();

        $response = $this->actingAs($coach)->get('/class/' . $class->id . '/attendance');

        $response->assertStatus(200);
    }

    /**
     * Test non-coach cannot view class attendance.
     */
    public function test_non_coach_cannot_view_class_attendance(): void
    {
        $user = User::factory()->create(['is_coach' => false]);
        $class = ClassSession::factory()->create();

        $response = $this->actingAs($user)->get('/class/' . $class->id . '/attendance');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
