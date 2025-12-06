<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\Coupon|null $coupon
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage query()
 * @property int $id
 * @property int $coupon_id
 * @property int $order_id
 * @property int $user_id
 * @property string $discount_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUsage whereUserId($value)
 * @mixin \Eloquent
 */
class CouponUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'order_id',
        'user_id',
        'discount_amount',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
