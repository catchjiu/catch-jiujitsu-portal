<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\MembershipPackage;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can access overview dashboard.
     */
    public function test_admin_can_access_overview_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    /**
     * Test admin can view members list.
     */
    public function test_admin_can_view_members_list(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/members');

        $response->assertStatus(200);
    }

    /**
     * Test admin can search members.
     */
    public function test_admin_can_search_members(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        User::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

        $response = $this->actingAs($admin)->get('/admin/members?search=John');

        $response->assertStatus(200);
        $response->assertSee('John');
        $response->assertDontSee('Jane');
    }

    /**
     * Test admin can filter members by age group.
     */
    public function test_admin_can_filter_members_by_age_group(): void
    {
        $admin = User::factory()->admin()->create();
        $adultMember = User::factory()->create([
            'first_name' => 'AdultMember',
            'last_name' => 'TestUser',
            'age_group' => 'Adults'
        ]);
        $kidMember = User::factory()->kids()->create([
            'first_name' => 'KidMember',
            'last_name' => 'TestChild'
        ]);

        $response = $this->actingAs($admin)->get('/admin/members?age=Kids');

        $response->assertStatus(200);
        // The kids member should be visible
        $response->assertSee('KidMember');
        // The adult member should NOT be in the filtered results
        $response->assertDontSee('AdultMember');
    }

    /**
     * Test admin can filter members by status.
     */
    public function test_admin_can_filter_members_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->withActiveMembership()->create(['first_name' => 'Active']);
        User::factory()->withExpiredMembership()->create(['first_name' => 'Expired']);

        $response = $this->actingAs($admin)->get('/admin/members?status=active');

        $response->assertStatus(200);
        $response->assertSee('Active');
        $response->assertDontSee('Expired');
    }

    /**
     * Test admin can create a new member.
     */
    public function test_admin_can_create_new_member(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/members', [
            'first_name' => 'New',
            'last_name' => 'Member',
            'email' => 'new@example.com',
            'password' => 'password123',
            'age_group' => 'Adults',
            'rank' => 'White',
            'stripes' => 0,
        ]);

        $response->assertRedirect('/admin/members');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'first_name' => 'New',
            'last_name' => 'Member',
            'email' => 'new@example.com',
        ]);
    }

    /**
     * Test admin can update member details.
     */
    public function test_admin_can_update_member_details(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['first_name' => 'Old']);

        $response = $this->actingAs($admin)->put('/admin/members/' . $member->id, [
            'first_name' => 'Updated',
            'last_name' => $member->last_name,
            'email' => $member->email,
            'age_group' => 'Adults',
            'rank' => 'Blue',
            'stripes' => 2,
            'mat_hours' => 100,
            'discount_type' => 'none',
            'discount_amount' => 0,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $member->refresh();
        $this->assertEquals('Updated', $member->first_name);
        $this->assertEquals('Blue', $member->rank);
        $this->assertEquals(2, $member->stripes);
    }

    /**
     * Test admin can delete a member.
     */
    public function test_admin_can_delete_member(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
        $memberId = $member->id;

        // Create some related records
        Payment::factory()->forUser($member)->create();
        $class = ClassSession::factory()->create();
        Booking::factory()->forUser($member)->forClass($class)->create();

        $response = $this->actingAs($admin)->delete('/admin/members/' . $memberId);

        $response->assertRedirect('/admin/members');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $memberId]);
        $this->assertDatabaseMissing('payments', ['user_id' => $memberId]);
        $this->assertDatabaseMissing('bookings', ['user_id' => $memberId]);
    }

    /**
     * Test admin can set member as coach.
     */
    public function test_admin_can_set_member_as_coach(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['is_coach' => false]);

        $response = $this->actingAs($admin)->put('/admin/members/' . $member->id, [
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'email' => $member->email,
            'age_group' => 'Adults',
            'rank' => 'White',
            'stripes' => 0,
            'mat_hours' => 0,
            'is_coach' => true,
            'discount_type' => 'none',
            'discount_amount' => 0,
        ]);

        $member->refresh();
        $this->assertTrue($member->is_coach);
    }

    /**
     * Test admin can set member discount.
     */
    public function test_admin_can_set_member_discount(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $response = $this->actingAs($admin)->put('/admin/members/' . $member->id, [
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'email' => $member->email,
            'age_group' => 'Adults',
            'rank' => 'White',
            'stripes' => 0,
            'mat_hours' => 0,
            'discount_type' => 'gratis',
            'discount_amount' => 0,
        ]);

        $member->refresh();
        $this->assertEquals('gratis', $member->discount_type);
    }

    // ========== CLASS MANAGEMENT TESTS ==========

    /**
     * Test admin can view classes page.
     */
    public function test_admin_can_view_classes_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/classes');

        $response->assertStatus(200);
    }

    /**
     * Test admin can create a class.
     */
    public function test_admin_can_create_class(): void
    {
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();

        $response = $this->actingAs($admin)->post('/admin/classes', [
            'title' => 'Morning BJJ',
            'type' => 'Gi',
            'age_group' => 'Adults',
            'instructor_id' => $coach->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '09:00',
            'duration_minutes' => 90,
            'capacity' => 20,
            'recurring' => false,
        ]);

        $response->assertRedirect('/admin/classes');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('classes', [
            'title' => 'Morning BJJ',
            'type' => 'Gi',
            'instructor_id' => $coach->id,
        ]);
    }

    /**
     * Test admin can create recurring classes.
     */
    public function test_admin_can_create_recurring_classes(): void
    {
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();

        $response = $this->actingAs($admin)->post('/admin/classes', [
            'title' => 'Weekly Class',
            'type' => 'No-Gi',
            'age_group' => 'Adults',
            'instructor_id' => $coach->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '18:00',
            'duration_minutes' => 60,
            'capacity' => 15,
            'recurring' => true,
        ]);

        $response->assertRedirect('/admin/classes');

        // Should create 5 classes (original + 4 weeks)
        $this->assertEquals(5, ClassSession::where('title', 'Weekly Class')->count());
    }

    /**
     * Test admin can update a class.
     */
    public function test_admin_can_update_class(): void
    {
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();
        $class = ClassSession::factory()->create(['title' => 'Old Title']);

        $response = $this->actingAs($admin)->put('/admin/classes/' . $class->id, [
            'title' => 'New Title',
            'type' => 'Gi',
            'age_group' => 'Adults',
            'instructor_id' => $coach->id,
            'capacity' => 25,
        ]);

        $response->assertRedirect('/admin/classes');

        $class->refresh();
        $this->assertEquals('New Title', $class->title);
        $this->assertEquals(25, $class->capacity);
    }

    /**
     * Test admin can cancel a class.
     */
    public function test_admin_can_cancel_class(): void
    {
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();
        $class = ClassSession::factory()->create(['is_cancelled' => false]);

        $response = $this->actingAs($admin)->put('/admin/classes/' . $class->id, [
            'title' => $class->title,
            'type' => $class->type,
            'age_group' => $class->age_group,
            'instructor_id' => $coach->id,
            'capacity' => $class->capacity,
            'is_cancelled' => true,
        ]);

        $class->refresh();
        $this->assertTrue($class->is_cancelled);
    }

    /**
     * Test admin can delete a class.
     */
    public function test_admin_can_delete_class(): void
    {
        $admin = User::factory()->admin()->create();
        $class = ClassSession::factory()->create();
        $classId = $class->id;

        $response = $this->actingAs($admin)->delete('/admin/classes/' . $classId);

        $response->assertRedirect('/admin/classes');

        $this->assertDatabaseMissing('classes', ['id' => $classId]);
    }

    // ========== ATTENDANCE TESTS ==========

    /**
     * Test admin can view class attendance.
     */
    public function test_admin_can_view_class_attendance(): void
    {
        $admin = User::factory()->admin()->create();
        $class = ClassSession::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/attendance/' . $class->id);

        $response->assertStatus(200);
    }

    /**
     * Test admin can toggle member check-in.
     */
    public function test_admin_can_toggle_member_checkin(): void
    {
        $admin = User::factory()->admin()->create();
        $class = ClassSession::factory()->create();
        $member = User::factory()->create();
        $booking = Booking::factory()->forUser($member)->forClass($class)->create(['checked_in' => false]);

        $response = $this->actingAs($admin)->post('/admin/attendance/' . $class->id . '/toggle/' . $booking->id);

        $response->assertRedirect();

        $booking->refresh();
        // Use assertEquals with boolean cast since DB may return int
        $this->assertEquals(true, (bool) $booking->checked_in);
    }

    // ========== FINANCE TESTS ==========

    /**
     * Test admin can view finance dashboard.
     */
    public function test_admin_can_view_finance_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/finance');

        $response->assertStatus(200);
    }

    // ========== MEMBERSHIP PACKAGES TESTS ==========

    /**
     * Test admin can view packages page.
     */
    public function test_admin_can_view_packages_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/packages');

        $response->assertStatus(200);
    }

    /**
     * Test admin can create a package.
     */
    public function test_admin_can_create_package(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/packages', [
            'name' => 'New Package',
            'description' => 'A test package',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => 2000,
            'age_group' => 'All',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('membership_packages', [
            'name' => 'New Package',
            'price' => 2000,
        ]);
    }

    /**
     * Test admin can update a package.
     */
    public function test_admin_can_update_package(): void
    {
        $admin = User::factory()->admin()->create();
        $package = MembershipPackage::factory()->create(['name' => 'Old Name', 'price' => 1000]);

        $response = $this->actingAs($admin)->put('/admin/packages/' . $package->id, [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => 2500,
            'age_group' => 'All',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $response->assertRedirect();

        $package->refresh();
        $this->assertEquals('Updated Name', $package->name);
        $this->assertEquals(2500, $package->price);
    }

    /**
     * Test admin can toggle package status.
     */
    public function test_admin_can_toggle_package_status(): void
    {
        $admin = User::factory()->admin()->create();
        $package = MembershipPackage::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post('/admin/packages/' . $package->id . '/toggle');

        $response->assertRedirect();

        $package->refresh();
        $this->assertFalse($package->is_active);
    }

    /**
     * Test admin can delete a package.
     */
    public function test_admin_can_delete_package(): void
    {
        $admin = User::factory()->admin()->create();
        $package = MembershipPackage::factory()->create();
        $packageId = $package->id;

        $response = $this->actingAs($admin)->delete('/admin/packages/' . $packageId);

        $response->assertRedirect();

        $this->assertDatabaseMissing('membership_packages', ['id' => $packageId]);
    }

    // ========== AUTHORIZATION TESTS ==========

    /**
     * Test non-admin cannot access admin routes.
     */
    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $endpoints = [
            ['GET', '/admin'],
            ['GET', '/admin/members'],
            ['GET', '/admin/classes'],
            ['GET', '/admin/finance'],
            ['GET', '/admin/payments'],
            ['GET', '/admin/packages'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->actingAs($user)->$method($endpoint);
            $response->assertStatus(403);
        }
    }

    /**
     * Test guest cannot access admin routes.
     */
    public function test_guest_cannot_access_admin_routes(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }
}
