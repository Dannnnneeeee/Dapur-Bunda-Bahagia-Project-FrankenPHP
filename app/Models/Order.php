<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'order_type',
        'table_number',
        'notes',
        'subtotal',
        'tax',
        'discount',
        'total_price',
        'status',
        'completed_at',
        'cancelled_at',
        'cancelled_reason',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Boot method untuk auto-generate order number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Generate order number: INV-YYYYMMDD-XXX
     */
    public static function generateOrderNumber()
    {
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastOrder ? (int) substr($lastOrder->order_number, -3) + 1 : 1;

        return 'INV-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke User
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke OrderItems
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relasi ke Payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relasi ke Admin (created_by)
     */
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Calculate subtotal dari items
     */
    public function calculateSubtotal()
    {
        return $this->items->sum('subtotal');
    }

    /**
     * Calculate tax (PB1 10%)
     */
    public function calculateTax()
    {
        return $this->subtotal * 0.11;
    }

    /**
     * Calculate total price
     */
    public function calculateTotal()
    {
        return $this->subtotal + $this->tax - $this->discount;
    }

    /**
     * Recalculate semua pricing
     */
    public function recalculate()
    {
        $this->subtotal = $this->calculateSubtotal();
        $this->tax = $this->calculateTax();
        $this->total_price = $this->calculateTotal();
        $this->save();
    }

    /**
     * Mark as paid
     */
    public function markAsPaid()
    {
        // Check if fully paid
        if ($this->getTotalPaidAttribute() >= $this->total_price) {
            $this->update([
                'status' => 'preparing', // auto move to preparing when paid
            ]);
        }
    }

    /**
     * Check payment status dan update order
     */
    public function checkPaymentStatus()
    {
        $totalPaid = $this->getTotalPaidAttribute();

        if ($totalPaid >= $this->total_price) {
            // Fully paid
            if ($this->status === 'pending') {
                $this->update(['status' => 'preparing']);
            }
        }
    }

    /**
     * Get total amount paid
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->paid()->sum('amount');
    }

    /**
     * Get remaining amount to pay
     */
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->total_price - $this->getTotalPaidAttribute());
    }

    /**
     * Check if order is fully paid
     */
    public function isFullyPaid()
    {
        return $this->getTotalPaidAttribute() >= $this->total_price;
    }

    /**
     * Check if order has any payment
     */
    public function hasPayment()
    {
        return $this->payments()->exists();
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_reason' => $reason,
        ]);
    }

    /**
     * Scope untuk status
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePreparing($query)
    {
        return $query->where('status', 'preparing');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope untuk payment status
     */
    public function scopePaid($query)
    {
        return $query->whereHas('payments', function($q) {
            $q->where('status', 'paid');
        })->whereRaw('(SELECT SUM(amount) FROM payments WHERE order_id = orders.id AND status = "paid") >= total_price');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereDoesntHave('payments', function($q) {
            $q->where('status', 'paid');
        });
    }

    /**
     * Scope untuk order type
     */
    public function scopeDineIn($query)
    {
        return $query->where('order_type', 'dine_in');
    }

    public function scopeTakeaway($query)
    {
        return $query->where('order_type', 'takeaway');
    }

    /**
     * Accessor untuk customer name (prioritas user)
     */
    public function getCustomerDisplayNameAttribute()
    {
        return $this->user ? $this->user->name : $this->customer_name ?? 'Walk-in Customer';
    }

    /**
     * Accessor untuk status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'preparing' => 'info',
            'ready' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Accessor untuk payment status badge color
     */
    public function getPaymentStatusColorAttribute()
    {
        if ($this->isFullyPaid()) {
            return 'success';
        } elseif ($this->hasPayment()) {
            return 'warning'; // partial payment
        } else {
            return 'danger'; // unpaid
        }
    }

    /**
     * Accessor untuk payment status text
     */
    public function getPaymentStatusTextAttribute()
    {
        if ($this->isFullyPaid()) {
            return 'Paid';
        } elseif ($this->hasPayment()) {
            return 'Partial';
        } else {
            return 'Unpaid';
        }
    }
}
