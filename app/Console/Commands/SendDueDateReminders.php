<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDueDateReminders extends Command
{
    protected $signature = 'reminders:send-due-date';
    protected $description = 'Send SMS reminders for upcoming and overdue due dates';

    public function handle(SmsService $smsService)
    {
        $today = Carbon::today();

        // Get all unpaid transaction groups with due_date
        $groups = Transaction::where('status', '!=', 'PAID')
            ->whereNotNull('due_date')
            ->groupBy('group_id', 'customer_id', 'due_date')
            ->select('group_id', 'customer_id', 'due_date')
            ->get();

        foreach ($groups as $group) {
            $customer = $group->customer;
            if (!$customer || empty($customer->contact_number)) {
                continue; // skip if no phone number
            }

            if (!preg_match('/^\+639\d{9}$/', $customer->contact_number)) {
                continue;
            }


            $dueDate = Carbon::parse($group->due_date)->startOfDay();
            $daysUntilDue = $today->diffInDays($dueDate, false); // negative if overdue

            // 2 days before due date
            if ($daysUntilDue == 2) {
                $this->sendReminder($smsService, $customer, $group->group_id, 'pre_due', 0, $dueDate);
            }

            // Overdue weeks (every 7 days after due date)
            if ($daysUntilDue < 0) {
                $overdueDays = -$daysUntilDue; // positive days overdue
                $weekNumber = (int) ceil($overdueDays / 7); // 1 for first week, 2 for second, etc.
                // Check if we already sent a reminder for this week
                $sent = DB::table('transaction_reminders')
                    ->where('group_id', $group->group_id)
                    ->where('type', 'overdue_week')
                    ->where('week_number', $weekNumber)
                    ->exists();
                if (!$sent) {
                    $this->sendReminder($smsService, $customer, $group->group_id, 'overdue_week', $weekNumber, $dueDate);
                }
            }
        }

        $this->info('Due date reminders processed.');
        return 0;
    }

    private function sendReminder($smsService, $customer, $groupId, $type, $weekNumber, $dueDate)
    {
        $phone = $customer->contact_number;
        $storeName = config('app.name', 'Store');

        $message = match ($type) {
            'pre_due' => "Reminder: Your payment of ₱{$customer->outstanding_balance} is due in 2 days (due date: {$dueDate->format('Y-m-d')}). Please settle your account at {$storeName}.",
            'overdue_week' => "Overdue reminder: Your payment of ₱{$customer->outstanding_balance} was due on {$dueDate->format('Y-m-d')} and is now overdue. Please make payment at {$storeName}.",
        };

        $sent = $smsService->send($phone, $message);

        if ($sent) {
            DB::table('transaction_reminders')->insert([
                'group_id' => $groupId,
                'type' => $type,
                'week_number' => $weekNumber,
                'sent_at' => now(),
            ]);
        }
    }
}
