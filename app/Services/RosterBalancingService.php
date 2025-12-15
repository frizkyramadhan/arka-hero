<?php

namespace App\Services;

use App\Models\Roster;
use App\Models\RosterAdjustment;
use App\Models\RosterDailyStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RosterBalancingService
{
    /**
     * Apply manual balancing untuk work_days
     */
    public function applyBalancing($rosterId, $days, $reason, $effectiveDate = null)
    {
        $roster = Roster::findOrFail($rosterId);
        
        // Validate: days tidak boleh 0
        if ($days == 0) {
            throw new \InvalidArgumentException('Adjustment days cannot be zero');
        }
        
        DB::beginTransaction();
        try {
            // Create adjustment record
            $adjustment = RosterAdjustment::create([
                'roster_id' => $rosterId,
                'leave_request_id' => null, // Manual balancing
                'adjustment_type' => $days > 0 ? '+days' : '-days',
                'adjusted_value' => abs($days),
                'reason' => "Manual balancing: {$reason}",
                'effective_date' => $effectiveDate ? Carbon::parse($effectiveDate) : Carbon::now(),
            ]);
            
            // Update roster adjusted_days
            $roster->updateAdjustedDays();
            
            // Shift periodic leave dates in roster_daily_status
            $this->shiftPeriodicLeaveDates($roster, $days, $effectiveDate);
            
            DB::commit();
            return $adjustment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Apply balancing untuk multiple rosters
     */
    public function applyBulkBalancing(array $rosterIds, $days, $reason, $effectiveDate = null)
    {
        $results = [];
        $errors = [];
        
        foreach ($rosterIds as $rosterId) {
            try {
                $adjustment = $this->applyBalancing($rosterId, $days, $reason, $effectiveDate);
                $results[] = [
                    'roster_id' => $rosterId,
                    'success' => true,
                    'adjustment_id' => $adjustment->id
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'roster_id' => $rosterId,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => count($errors) === 0,
            'results' => $results,
            'errors' => $errors
        ];
    }
    
    /**
     * Estimate next periodic leave date berdasarkan adjusted work_days
     */
    public function estimateNextPeriodicLeave($roster, $fromDate = null)
    {
        $fromDate = $fromDate ? Carbon::parse($fromDate) : now();
        $adjustedWorkDays = $roster->getAdjustedWorkDays();
        $offDays = $roster->getOffDays(); // Tetap 14 hari
        
        // Hitung kapan cycle berikutnya selesai
        $workPeriodEnd = $fromDate->copy()->addDays($adjustedWorkDays);
        
        // Periodic leave mulai setelah work_days selesai
        $periodicLeaveStart = $workPeriodEnd->copy()->addDay();
        $periodicLeaveEnd = $periodicLeaveStart->copy()->addDays($offDays - 1);
        
        return [
            'work_period_start' => $fromDate,
            'work_period_end' => $workPeriodEnd,
            'periodic_leave_start' => $periodicLeaveStart,
            'periodic_leave_end' => $periodicLeaveEnd,
            'adjusted_work_days' => $adjustedWorkDays,
            'off_days' => $offDays,
            'total_cycle_days' => $adjustedWorkDays + $offDays
        ];
    }
    
    /**
     * Get balancing history untuk roster
     */
    public function getHistory($rosterId)
    {
        return RosterAdjustment::where('roster_id', $rosterId)
            ->whereNull('leave_request_id') // Hanya manual balancing
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Shift periodic leave dates in roster_daily_status
     * Menggeser semua status 'C' (Periodic Leave) sesuai dengan adjustment days
     */
    protected function shiftPeriodicLeaveDates(Roster $roster, $days, $effectiveDate = null)
    {
        $effectiveDate = $effectiveDate ? Carbon::parse($effectiveDate)->startOfDay() : Carbon::now()->startOfDay();
        
        // Cari semua status 'C' yang >= effective_date
        // Kita akan geser semua status 'C' yang berurutan pertama yang ditemukan
        $leaveDates = RosterDailyStatus::where('roster_id', $roster->id)
            ->where('status_code', 'C')
            ->where('date', '>=', $effectiveDate)
            ->orderBy('date', 'asc')
            ->get();
        
        if ($leaveDates->isEmpty()) {
            return; // Tidak ada cuti untuk digeser
        }
        
        // Cari semua status 'C' yang berurutan mulai dari tanggal pertama
        $consecutiveLeaveDates = [];
        $expectedDate = null;
        
        foreach ($leaveDates as $leaveDate) {
            $leaveDateCarbon = Carbon::parse($leaveDate->date)->startOfDay();
            
            if (empty($consecutiveLeaveDates)) {
                // Ini adalah tanggal pertama
                $consecutiveLeaveDates[] = $leaveDate;
                $expectedDate = $leaveDateCarbon->copy()->addDay();
            } elseif ($leaveDateCarbon->equalTo($expectedDate)) {
                // Tanggal ini berurutan dengan tanggal sebelumnya
                $consecutiveLeaveDates[] = $leaveDate;
                $expectedDate->addDay();
            } else {
                // Tidak berurutan, stop (hanya geser cuti yang berurutan pertama)
                break;
            }
        }
        
        if (empty($consecutiveLeaveDates)) {
            return;
        }
        
        // Geser semua status 'C' yang berurutan
        foreach ($consecutiveLeaveDates as $leaveDate) {
            $oldDate = Carbon::parse($leaveDate->date)->startOfDay();
            $newDate = $oldDate->copy()->addDays($days);
            
            // Simpan notes lama jika ada
            $oldNotes = $leaveDate->notes;
            
            // Hapus status lama
            RosterDailyStatus::where('roster_id', $roster->id)
                ->where('date', $oldDate->format('Y-m-d'))
                ->delete();
            
            // Buat status baru di tanggal yang baru
            $newNotes = $oldNotes 
                ? "Shifted from {$oldDate->format('Y-m-d')}: {$oldNotes}" 
                : "Shifted from {$oldDate->format('Y-m-d')}";
            
            RosterDailyStatus::updateOrCreate(
                [
                    'roster_id' => $roster->id,
                    'date' => $newDate->format('Y-m-d')
                ],
                [
                    'status_code' => 'C',
                    'notes' => $newNotes
                ]
            );
            
            // Jika days > 0 (cuti diundur), ubah tanggal-tanggal yang di antara menjadi 'D'
            if ($days > 0) {
                $tempDate = $oldDate->copy()->addDay();
                while ($tempDate->lt($newDate)) {
                    // Cek apakah tanggal ini sudah ada status lain
                    $existingStatus = RosterDailyStatus::where('roster_id', $roster->id)
                        ->where('date', $tempDate->format('Y-m-d'))
                        ->first();
                    
                    // Hanya update jika tidak ada status atau status adalah 'C' (yang sudah dihapus)
                    if (!$existingStatus || $existingStatus->status_code === 'C') {
                        // Update atau create status 'D' untuk hari kerja tambahan
                        RosterDailyStatus::updateOrCreate(
                            [
                                'roster_id' => $roster->id,
                                'date' => $tempDate->format('Y-m-d')
                            ],
                            [
                                'status_code' => 'D',
                                'notes' => 'Work day added due to leave adjustment'
                            ]
                        );
                    }
                    
                    $tempDate->addDay();
                }
            } elseif ($days < 0) {
                // Jika days < 0 (cuti dimajukan), tanggal-tanggal yang di antara perlu diubah menjadi 'D'
                // karena cuti sudah dipindah ke tanggal yang lebih awal
                $tempDate = $newDate->copy()->addDays(abs($days));
                $endDate = $oldDate->copy();
                
                while ($tempDate->lt($endDate)) {
                    $existingStatus = RosterDailyStatus::where('roster_id', $roster->id)
                        ->where('date', $tempDate->format('Y-m-d'))
                        ->first();
                    
                    if (!$existingStatus || $existingStatus->status_code === 'C') {
                        RosterDailyStatus::updateOrCreate(
                            [
                                'roster_id' => $roster->id,
                                'date' => $tempDate->format('Y-m-d')
                            ],
                            [
                                'status_code' => 'D',
                                'notes' => 'Work day added due to leave adjustment'
                            ]
                        );
                    }
                    
                    $tempDate->addDay();
                }
            }
        }
    }
}

