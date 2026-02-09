<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #{{ $order->id }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.5; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        h1 { font-size: 1.25rem; margin-bottom: 0.5rem; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { text-align: left; padding: 0.5rem; border-bottom: 1px solid #eee; }
        th { font-weight: 600; color: #666; }
        .total { font-weight: bold; font-size: 1.1rem; }
        .meta { color: #666; font-size: 0.875rem; margin-top: 1rem; }
    </style>
</head>
<body>
    <h1>New shop order #{{ $order->id }}</h1>
    <p><strong>Member:</strong> {{ $order->user->name }}{{ $order->user->chinese_name ? ' (' . $order->user->chinese_name . ')' : '' }}</p>
    <p><strong>Email:</strong> {{ $order->user->email }}</p>
    <p><strong>Date:</strong> {{ $order->created_at->format('M j, Y g:i A') }}</p>
    <p><strong>Status:</strong> {{ $order->status }}</p>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Size / Color</th>
                <th>Qty</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                @php
                    $v = $item->productVariant;
                    $p = $v->product ?? null;
                    $name = $p ? $p->name : 'Product #' . $v->product_id;
                @endphp
                <tr>
                    <td>{{ $name }}{{ $item->is_preorder ? ' (Pre-order)' : '' }}</td>
                    <td>{{ $v->size }}{{ $v->color ? ' · ' . $v->color : '' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>NT$ {{ number_format($item->unit_price * $item->quantity) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="total">Total: NT$ {{ number_format($order->total_price) }}</p>

    <p class="meta">View and manage orders in Admin → Order Tracker.</p>
</body>
</html>
