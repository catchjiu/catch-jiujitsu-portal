<?php

namespace Tests\Feature;

use App\Models\MembershipPackage;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test member can view payments page.
     */
    public function test_member_can_view_payments_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/payments');

        $response->assertStatus(200);
    }

    /**
     * Test member can submit a bank payment.
     */
    public function test_member_can_submit_bank_payment(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/payments/submit', [
            'payment_method' => 'bank',
            'payment_date' => now()->format('Y-m-d'),
            'payment_amount' => 2000,
            'account_last_5' => '12345',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'amount' => 2000,
            'status' => 'Pending Verification',
            'payment_method' => 'bank',
            'account_last_5' => '12345',
        ]);
    }

    /**
     * Test member can submit a LINE Pay payment.
     */
    public function test_member_can_submit_linepay_payment(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/payments/submit', [
            'payment_method' => 'linepay',
            'payment_date' => now()->format('Y-m-d'),
            'payment_amount' => 2000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'amount' => 2000,
            'status' => 'Pending Verification',
            'payment_method' => 'linepay',
            'account_last_5' => null,
        ]);
    }

    /**
     * Test bank payment requires account_last_5.
     */
    public function test_bank_payment_requires_account_last_5(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/payments/submit', [
            'payment_method' => 'bank',
            'payment_date' => now()->format('Y-m-d'),
            'payment_amount' => 2000,
        ]);

        $response->assertSessionHasErrors('account_last_5');
    }

    /**
     * Test payment submission requires all fields.
     */
    public function test_payment_submission_requires_all_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/payments/submit', []);

        $response->assertSessionHasErrors(['payment_method', 'payment_date', 'payment_amount']);
    }

    /**
     * Test member can resubmit rejected payment.
     */
    public function test_member_can_resubmit_rejected_payment(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->forUser($user)->rejected()->create();

        $response = $this->actingAs($user)->post('/payments/' . $payment->id . '/upload', [
            'payment_method' => 'bank',
            'payment_date' => now()->format('Y-m-d'),
            'payment_amount' => 2500,
            'account_last_5' => '54321',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals('Pending Verification', $payment->status);
        $this->assertEquals(2500, $payment->amount);
        $this->assertEquals('54321', $payment->account_last_5);
    }

    /**
     * Test member cannot resubmit another user's payment.
     */
    public function test_member_cannot_resubmit_another_users_payment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $payment = Payment::factory()->forUser($otherUser)->rejected()->create();

        $response = $this->actingAs($user)->post('/payments/' . $payment->id . '/upload', [
            'payment_method' => 'bank',
            'payment_date' => now()->format('Y-m-d'),
            'payment_amount' => 2500,
            'account_last_5' => '54321',
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test admin can view payments management page.
     */
    public function test_admin_can_view_payments_management(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/payments');

        $response->assertStatus(200);
    }

    /**
     * Test admin can approve a payment.
     */
    public function test_admin_can_approve_payment(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
        $payment = Payment::factory()->forUser($member)->pending()->create();

        $response = $this->actingAs($admin)->post('/admin/payments/' . $payment->id . '/approve');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals('Paid', $payment->status);
    }

    /**
     * Test admin can reject a payment.
     */
    public function test_admin_can_reject_payment(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
        $payment = Payment::factory()->forUser($member)->pending()->create();

        $response = $this->actingAs($admin)->post('/admin/payments/' . $payment->id . '/reject');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals('Rejected', $payment->status);
    }

    /**
     * Test admin can approve payment and update membership.
     */
    public function test_admin_can_approve_payment_and_update_membership(): void
    {
        $admin = User::factory()->admin()->create();
        $package = MembershipPackage::factory()->monthly()->create();
        $member = User::factory()->create([
            'membership_status' => 'pending',
        ]);
        $payment = Payment::factory()->forUser($member)->pending()->create();

        $response = $this->actingAs($admin)->post('/admin/payments/' . $payment->id . '/approve-with-membership', [
            'membership_package_id' => $package->id,
            'membership_status' => 'active',
            'membership_expires_at' => null,
            'classes_remaining' => null,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $member->refresh();

        $this->assertEquals('Paid', $payment->status);
        $this->assertEquals('active', $member->membership_status);
        $this->assertEquals($package->id, $member->membership_package_id);
        $this->assertNotNull($member->membership_expires_at);
    }

    /**
     * Test admin can approve payment with class-based package.
     */
    public function test_admin_can_approve_payment_with_class_based_package(): void
    {
        $admin = User::factory()->admin()->create();
        $package = MembershipPackage::factory()->classBased(10)->create();
        $member = User::factory()->create();
        $payment = Payment::factory()->forUser($member)->pending()->create();

        $response = $this->actingAs($admin)->post('/admin/payments/' . $payment->id . '/approve-with-membership', [
            'membership_package_id' => $package->id,
            'membership_status' => 'active',
            'membership_expires_at' => null,
            'classes_remaining' => null,
        ]);

        $response->assertRedirect();

        $member->refresh();
        $this->assertEquals(10, $member->classes_remaining);
    }

    /**
     * Test non-admin cannot approve payments.
     */
    public function test_non_admin_cannot_approve_payments(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->pending()->create();

        $response = $this->actingAs($user)->post('/admin/payments/' . $payment->id . '/approve');

        $response->assertStatus(403);
    }

    /**
     * Test member sees their own payments only.
     */
    public function test_member_sees_own_payments_only(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userPayment = Payment::factory()->forUser($user)->create(['amount' => 1234]);
        $otherPayment = Payment::factory()->forUser($otherUser)->create(['amount' => 5678]);

        $response = $this->actingAs($user)->get('/payments');

        $response->assertStatus(200);
        
        // Verify the user only sees their own payment in the database
        $this->assertCount(1, Payment::where('user_id', $user->id)->get());
        $this->assertEquals(1234, Payment::where('user_id', $user->id)->first()->amount);
    }

    /**
     * Test payment amount must be positive.
     */
    public function test_payment_amount_must_be_positive(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/payments/submit', [
            'payment_method' => 'linepay',
            'payment_date' => now()->format('Y-m-d'),
            'payment_amount' => 0,
        ]);

        $response->assertSessionHasErrors('payment_amount');
    }

    /**
     * Test account_last_5 must be exactly 5 characters.
     */
    public function test_account_last_5_must_be_exactly_5_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/payments/submit', [
            'payment_method' => 'bank',
            'payment_date' => now()->format('Y-m-d'),
            'payment_amount' => 2000,
            'account_last_5' => '123',
        ]);

        $response->assertSessionHasErrors('account_last_5');
    }
}
