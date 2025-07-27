<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KriteriaBobot extends Model
{
    use HasFactory;

    protected $table = 'kriteria_bobot';

    protected $primaryKey = 'id_kriteria';
    public $incrementing = false; // Since id_guru is not auto-incrementing
    protected $keyType = 'string';

    protected $fillable = [
        'id_kriteria', 'kriteria', 'bobot', 'status', 'createby',
        'approved_by', 'approved_at', 'rejection_reason', 'submitted_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'submitted_at' => 'datetime'
    ];

    /**
     * Get the user who approved this criteria
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who created this criteria
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'createby');
    }

    /**
     * Check if criteria is pending approval
     */
    public function isPending()
    {
        return $this->status === 'Menunggu';
    }

    /**
     * Check if criteria is approved
     */
    public function isApproved()
    {
        return $this->status === 'Disetujui';
    }

    /**
     * Check if criteria is rejected
     */
    public function isRejected()
    {
        return $this->status === 'Ditolak';
    }

    /**
     * Approve the criteria
     */
    public function approve($userId, $reason = null)
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => 'Disetujui',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => null
        ]);

        return $this;
    }

    /**
     * Reject the criteria
     */
    public function reject($userId, $reason)
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => 'Ditolak',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason
        ]);

        return $this;
    }

    /**
     * Submit for approval
     */
    public function submitForApproval($userId)
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => 'Menunggu',
            'submitted_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null
        ]);

        return $this;
    }

    /**
     * Get pending criteria for approval
     */
    public static function getPendingApprovals()
    {
        return static::where('status', 'Menunggu')
            ->with(['creator', 'auditTrail.user'])
            ->orderBy('submitted_at', 'asc')
            ->get();
    }

    /**
     * Get approved criteria
     */
    public static function getApproved()
    {
        return static::where('status', 'Disetujui')
            ->with(['approver'])
            ->orderBy('approved_at', 'desc')
            ->get();
    }

    /**
     * Get rejected criteria
     */
    public static function getRejected()
    {
        return static::where('status', 'Ditolak')
            ->with(['approver'])
            ->orderBy('approved_at', 'desc')
            ->get();
    }
}
