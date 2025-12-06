<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Coupon;
use App\Models\CouponUsage;

/**
 * @property int $id
 * @property string $order_number
 * @property int $user_id
 * @property string $total_amount
 * @property string $status
 * @property string $payment_status
 * @property string|null $payment_method
 * @property string $shipping_name
 * @property string $shipping_phone
 * @property string $shipping_address
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $shipped_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Coupon|null $coupon
 * @property-read CouponUsage|null $couponUsage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order paymentStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order status($status)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 * @property string $subtotal_amount
 * @property string $discount_amount
 * @property string $shipping_amount
 * @property int|null $coupon_id
 * @property string|null $coupon_code
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCouponCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSubtotalAmount($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'subtotal_amount',
        'discount_amount',
        'shipping_amount',
        'total_amount',
        'coupon_id',
        'coupon_code',
        'status',
        'payment_status',
        'payment_method',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items for the order.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
    }

    /**
     * Calculate and update total amount from order items.
     */
    public function calculateTotal()
    {
        $subtotal = $this->orderItems()->sum('total');
        $total = max(0, $subtotal + $this->shipping_amount - $this->discount_amount);

        $this->update([
            'subtotal_amount' => $subtotal,
            'total_amount' => $total,
        ]);

        return $total;
    }

    /**
     * Mark order as shipped.
     */
    public function markAsShipped()
    {
        $this->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);
    }

    /**
     * Mark order as delivered.
     */
    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Cancel the order.
     */
    public function cancel()
    {
        if ($this->status === 'pending' || $this->status === 'processing') {
            $this->update(['status' => 'cancelled']);

            if ($this->couponUsage) {
                $this->couponUsage()->delete();
            }
            
            // Restore product quantities
            foreach ($this->orderItems as $item) {
                $item->product->increment('quantity', $item->quantity);
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by payment status.
     */
    public function scopePaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }
}
