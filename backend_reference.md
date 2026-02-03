# Catch Jiu Jitsu - Laravel Backend Reference

This file contains the Database Schema (Migrations) and Controllers required for the 20i Hosting environment running Laravel 11.

## 1. Database Migrations

### Users Table
`database/migrations/xxxx_xx_xx_create_users_table.php`

```php
public function up(): void
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->enum('rank', ['White', 'Blue', 'Purple', 'Brown', 'Black'])->default('White');
        $table->integer('stripes')->default(0);
        $table->integer('mat_hours')->default(0);
        $table->boolean('is_admin')->default(false);
        $table->string('avatar_url')->nullable();
        $table->timestamps();
    });
}
```

### Classes Table
`database/migrations/xxxx_xx_xx_create_classes_table.php`

```php
public function up(): void
{
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // e.g., Fundamentals, Open Mat
        $table->string('type'); // Gi, No-Gi
        $table->dateTime('start_time');
        $table->integer('duration_minutes')->default(60);
        $table->string('instructor_name');
        $table->integer('capacity')->default(20);
        $table->timestamps();
    });
}
```

### Bookings Table
`database/migrations/xxxx_xx_xx_create_bookings_table.php`

```php
public function up(): void
{
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
        $table->timestamp('booked_at')->useCurrent();
        $table->unique(['user_id', 'class_id']); // Prevent double booking
    });
}
```

### Payments Table
`database/migrations/xxxx_xx_xx_create_payments_table.php`

```php
public function up(): void
{
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->decimal('amount', 8, 2);
        $table->string('month'); // e.g., "10-2023"
        $table->enum('status', ['Pending Verification', 'Paid', 'Overdue', 'Rejected'])->default('Overdue');
        $table->string('proof_image_path')->nullable();
        $table->timestamp('submitted_at')->nullable();
        $table->timestamps();
    });
}
```

---

## 2. Laravel Controllers

### BookingController
`app/Http/Controllers/BookingController.php`

```php
namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        // Get classes with booked count and user booking status
        return ClassSession::withCount('bookings')
            ->withExists(['bookings as is_booked_by_user' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate(['class_id' => 'required|exists:classes,id']);
        
        $class = ClassSession::withCount('bookings')->findOrFail($request->class_id);
        
        // Capacity Logic
        if ($class->bookings_count >= $class->capacity) {
            return response()->json(['message' => 'Class is full'], 422);
        }

        Booking::create([
            'user_id' => Auth::id(),
            'class_id' => $class->id
        ]);

        return response()->json(['message' => 'Class booked successfully']);
    }

    public function destroy($classId)
    {
        Booking::where('user_id', Auth::id())
               ->where('class_id', $classId)
               ->delete();

        return response()->json(['message' => 'Booking cancelled']);
    }
}
```

### PaymentController
`app/Http/Controllers/PaymentController.php`

```php
namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
    }

    public function uploadProof(Request $request, $paymentId)
    {
        $request->validate([
            'proof_image' => 'required|image|max:2048', // Max 2MB
        ]);

        $payment = Payment::where('user_id', Auth::id())->findOrFail($paymentId);

        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('payment_proofs', 'public');
            
            $payment->update([
                'proof_image_path' => $path,
                'status' => 'Pending Verification',
                'submitted_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Proof uploaded successfully']);
    }
}
```

### AdminController
`app/Http/Controllers/AdminController.php`

```php
namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function getPendingPayments()
    {
        return Payment::with('user')
            ->where('status', 'Pending Verification')
            ->orderBy('submitted_at')
            ->get();
    }

    public function approvePayment($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => 'Paid']);
        return response()->json(['message' => 'Payment approved']);
    }

    public function rejectPayment($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => 'Rejected']);
        // Optional: Delete image to save space
        return response()->json(['message' => 'Payment rejected']);
    }
}
```
