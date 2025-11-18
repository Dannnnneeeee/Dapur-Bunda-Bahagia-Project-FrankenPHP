<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'payment_number',
        'amount',
        'payment_method',
        'status',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_snap_token',
        'midtrans_payment_type',
        'midtrans_response',
        'paid_at',
        'expired_at',
        'notes',
        'reference_number',
        'cash_received',
        'change',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'midtrans_response' => 'array',
        'cash_received' => 'decimal:2',
        'change' => 'decimal:2',
    ];

    /**
     * Boot method untuk auto-generate payment number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = self::generatePaymentNumber();
            }
                    // Auto-calculate change untuk cash
             if ($payment->payment_method === 'cash' && $payment->cash_received && $payment->amount) {
                $payment->change = max(0, $payment->cash_received - $payment->amount);
            }
        });

        static::updating(function ($payment) {
            // Recalculate change saat update
            if ($payment->payment_method === 'cash' && $payment->cash_received && $payment->amount) {
                $payment->change = max(0, $payment->cash_received - $payment->amount);
            } else {
                $payment->change = null;
            }

        });
    }

    /**
     * Generate payment number: PAY-YYYYMMDD-XXX
     */
    public static function generatePaymentNumber()
    {
        $date = now()->format('Ymd');
        $lastPayment = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastPayment ? (int) substr($lastPayment->payment_number, -3) + 1 : 1;

        return 'PAY-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi ke Admin (processed_by)
     */
    public function processor()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    /**
     * Mark as paid
     */
    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Update order payment status
        $this->order->checkPaymentStatus();
    }

    /**
     * Mark as failed
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Mark as expired
     */
    public function markAsExpired()
    {
        $this->update([
            'status' => 'expired',
            'expired_at' => now(),
        ]);
    }

    /**
     * Process refund
     */
    public function refund()
    {
        $this->update([
            'status' => 'refunded',
        ]);

        // Update order payment status
        $this->order->checkPaymentStatus();
    }

    /**
     * Scope untuk status
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    /**
     * Scope untuk payment method
     */
    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    public function scopeMidtrans($query)
    {
        return $query->where('payment_method', 'midtrans');
    }

    /**
     * Accessor untuk status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'paid' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'expired' => 'secondary',
            'refunded' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Check apakah payment via Midtrans
     */
    public function isMidtrans()
    {
        return $this->payment_method === 'midtrans';
    }

    /**
     * Check apakah payment sudah lunas
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

}

