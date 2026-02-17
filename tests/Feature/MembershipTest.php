<?php

namespace Tests\Feature;

use App\Models\MembershipPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user with active status and valid expiry has active membership.
     */
    public function test_user_with_active_status_and_valid_expiry_has_active_membership(): void
    {
        $user = User::factory()->create([
            'membership_status' => 'active',
            'membership_expires_at' => now()->addMonth(),
        ]);

        $this->assertTrue($user->hasActiveMembership());
    }

    /**
     * Test user with active status but expired date has no active membership.
     */
    public function test_user_with_active_status_but_expired_date_has_no_active_membership(): void
    {
        $user = User::factory()->create([
            'membership_status' => 'active',
            'membership_expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($user->hasActiveMembership());
    }

    /**
     * Test user with expired status has no active membership.
     */
    public function test_user_with_expired_status_has_no_active_membership(): void
    {
        $user = User::factory()->withExpiredMembership()->create();

        $this->assertFalse($user->hasActiveMembership());
    }

    /**
     * Test user with pending status has no active membership.
     */
    public function test_user_with_pending_status_has_no_active_membership(): void
    {
        $user = User::factory()->create([
            'membership_status' => 'pending',
        ]);

        $this->assertFalse($user->hasActiveMembership());
    }

    /**
     * Test user with no membership has no active membership.
     */
    public function test_user_with_no_membership_has_no_active_membership(): void
    {
        $user = User::factory()->create([
            'membership_status' => 'none',
        ]);

        $this->assertFalse($user->hasActiveMembership());
    }

    /**
     * Test gratis user always has active membership.
     */
    public function test_gratis_user_always_has_active_membership(): void
    {
        $user = User::factory()->gratis()->create([
            'membership_status' => 'none', // Even with no status
            'membership_expires_at' => null,
        ]);

        $this->assertTrue($user->hasActiveMembership());
    }

    /**
     * Test gratis user has active membership even with expired date.
     */
    public function test_gratis_user_has_active_membership_even_with_expired_date(): void
    {
        $user = User::factory()->create([
            'discount_type' => 'gratis',
            'membership_status' => 'active',
            'membership_expires_at' => now()->subMonth(),
        ]);

        $this->assertTrue($user->hasActiveMembership());
    }

    /**
     * Test user with class-based package and classes remaining has active membership.
     */
    public function test_user_with_classes_remaining_has_active_membership(): void
    {
        $package = MembershipPackage::factory()->classBased(10)->create();
        $user = User::factory()->create([
            'membership_status' => 'active',
            'membership_package_id' => $package->id,
            'classes_remaining' => 5,
        ]);

        $this->assertTrue($user->hasActiveMembership());
    }

    /**
     * Test user with class-based package and zero classes has no active membership.
     */
    public function test_user_with_zero_classes_remaining_has_no_active_membership(): void
    {
        $package = MembershipPackage::factory()->classBased(10)->create();
        $user = User::factory()->create([
            'membership_status' => 'active',
            'membership_package_id' => $package->id,
            'classes_remaining' => 0,
        ]);

        $this->assertFalse($user->hasActiveMembership());
    }

    /**
     * Test isGratis returns true for gratis users.
     */
    public function test_is_gratis_returns_true_for_gratis_users(): void
    {
        $user = User::factory()->gratis()->create();

        $this->assertTrue($user->isGratis());
    }

    /**
     * Test isGratis returns false for regular users.
     */
    public function test_is_gratis_returns_false_for_regular_users(): void
    {
        $user = User::factory()->create(['discount_type' => 'none']);

        $this->assertFalse($user->isGratis());
    }

    /**
     * Test hasFixedDiscount returns true for users with fixed discount.
     */
    public function test_has_fixed_discount_returns_true_for_discounted_users(): void
    {
        $user = User::factory()->withDiscount(500)->create();

        $this->assertTrue($user->hasFixedDiscount());
    }

    /**
     * Test hasFixedDiscount returns false for users without discount.
     */
    public function test_has_fixed_discount_returns_false_for_regular_users(): void
    {
        $user = User::factory()->create(['discount_type' => 'none']);

        $this->assertFalse($user->hasFixedDiscount());
    }

    /**
     * Test membership issue message for no membership.
     */
    public function test_membership_issue_message_for_no_membership(): void
    {
        $user = User::factory()->create(['membership_status' => 'none']);

        $this->assertNotNull($user->membership_issue);
        $this->assertStringContainsString('No active membership', $user->membership_issue);
    }

    /**
     * Test membership issue message for pending membership.
     */
    public function test_membership_issue_message_for_pending_membership(): void
    {
        $user = User::factory()->create(['membership_status' => 'pending']);

        $this->assertNotNull($user->membership_issue);
        $this->assertStringContainsString('pending verification', $user->membership_issue);
    }

    /**
     * Test membership issue message for expired membership.
     */
    public function test_membership_issue_message_for_expired_membership(): void
    {
        $user = User::factory()->create(['membership_status' => 'expired']);

        $this->assertNotNull($user->membership_issue);
        $this->assertStringContainsString('expired', $user->membership_issue);
    }

    /**
     * Test membership issue message for zero classes remaining.
     */
    public function test_membership_issue_message_for_zero_classes(): void
    {
        $package = MembershipPackage::factory()->classBased(10)->create();
        $user = User::factory()->create([
            'membership_status' => 'active',
            'membership_package_id' => $package->id,
            'classes_remaining' => 0,
        ]);

        $this->assertNotNull($user->membership_issue);
        $this->assertStringContainsString('no classes remaining', $user->membership_issue);
    }

    /**
     * Test gratis users have no membership issue.
     */
    public function test_gratis_users_have_no_membership_issue(): void
    {
        $user = User::factory()->gratis()->create();

        $this->assertNull($user->membership_issue);
    }

    /**
     * Test decrementClassesRemaining works correctly.
     */
    public function test_decrement_classes_remaining_works(): void
    {
        $user = User::factory()->create(['classes_remaining' => 5]);

        $user->decrementClassesRemaining();

        $this->assertEquals(4, $user->fresh()->classes_remaining);
    }

    /**
     * Test decrementClassesRemaining does not go below zero.
     */
    public function test_decrement_classes_remaining_does_not_go_below_zero(): void
    {
        $user = User::factory()->create(['classes_remaining' => 0]);

        $user->decrementClassesRemaining();

        $this->assertEquals(0, $user->fresh()->classes_remaining);
    }

    /**
     * Test incrementClassesRemaining works for class-based packages.
     */
    public function test_increment_classes_remaining_works_for_class_based_packages(): void
    {
        $package = MembershipPackage::factory()->classBased(10)->create();
        $user = User::factory()->create([
            'membership_package_id' => $package->id,
            'classes_remaining' => 4,
        ]);

        $user->incrementClassesRemaining();

        $this->assertEquals(5, $user->fresh()->classes_remaining);
    }

    /**
     * Test admin can view member details.
     */
    public function test_admin_can_view_member_details(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/members/' . $member->id);

        $response->assertStatus(200);
    }

    /**
     * Test admin can update member membership.
     */
    public function test_admin_can_update_member_membership(): void
    {
        $admin = User::factory()->admin()->create();
        $package = MembershipPackage::factory()->monthly()->create();
        $member = User::factory()->create(['membership_status' => 'none']);

        $response = $this->actingAs($admin)->post('/admin/members/' . $member->id . '/membership', [
            'membership_package_id' => $package->id,
            'membership_status' => 'active',
            'membership_expires_at' => null,
            'classes_remaining' => null,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $member->refresh();
        $this->assertEquals('active', $member->membership_status);
        $this->assertEquals($package->id, $member->membership_package_id);
        $this->assertNotNull($member->membership_expires_at);
    }

    /**
     * Test membership package duration label for months.
     */
    public function test_membership_package_duration_label_for_months(): void
    {
        $package = MembershipPackage::factory()->create([
            'duration_type' => 'months',
            'duration_value' => 3,
        ]);

        $this->assertEquals('3 Months', $package->duration_label);
    }

    /**
     * Test membership package duration label for classes.
     */
    public function test_membership_package_duration_label_for_classes(): void
    {
        $package = MembershipPackage::factory()->classBased(10)->create();

        $this->assertEquals('10 Classes', $package->duration_label);
    }

    /**
     * Test membership package duration label singular.
     */
    public function test_membership_package_duration_label_singular(): void
    {
        $package = MembershipPackage::factory()->create([
            'duration_type' => 'months',
            'duration_value' => 1,
        ]);

        $this->assertEquals('1 Month', $package->duration_label);
    }

    /**
     * Test active scope on membership packages.
     */
    public function test_active_scope_on_membership_packages(): void
    {
        MembershipPackage::factory()->create(['is_active' => true]);
        MembershipPackage::factory()->create(['is_active' => true]);
        MembershipPackage::factory()->inactive()->create();

        $activePackages = MembershipPackage::active()->get();

        $this->assertCount(2, $activePackages);
    }
}
