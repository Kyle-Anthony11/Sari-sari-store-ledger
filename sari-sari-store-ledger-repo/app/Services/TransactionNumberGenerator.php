<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionNumberGenerator
{
    /**
     * Generate a unique 13-digit transaction number.
     *
     * Format: YYYYMMDD (8 digits) + NNNNN (5-digit daily sequence)
     * Example: 2026091200001
     */
    public function generate(): string
    {
        $today = Carbon::today()->toDateString();
        $datePrefix = Carbon::today()->format('Ymd'); // 8 digits

        return DB::transaction(function () use ($today, $datePrefix) {
            $sequence = DB::table('transaction_number_sequences')
                ->where('date', $today)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                DB::table('transaction_number_sequences')->insert([
                    'date'        => $today,
                    'last_number' => 1,
                ]);
                $nextNumber = 1;
            } else {
                $nextNumber = $sequence->last_number + 1;
                DB::table('transaction_number_sequences')
                    ->where('date', $today)
                    ->update(['last_number' => $nextNumber]);
            }

            // 8-digit date prefix + 5-digit zero-padded sequence = 13 digits
            return $datePrefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        });
    }
}
