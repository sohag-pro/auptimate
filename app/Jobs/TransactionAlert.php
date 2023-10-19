<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TransactionAlert implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct( public Transaction $transaction ) {}

    /**
     * Execute the job.
     */
    public function handle(): void {
        $this->updateCache();
        $transaction_threshold = config('transaction.threshold_amount', 1000);

        if ($this->transaction->amount > $transaction_threshold) {
            // Send an email/notification to the manager
            // $this->transaction->syndicate->manager->notify(new TransactionNotification());
            // logging for demo purposes
            info('Transaction amount exceeds threshold', ['transaction' => $this->transaction]);
        }
    }

    private function updateCache(){
        // get the last hour transactions from cache
        $last_hour_transactions = cache()->get('last_hour_transactions', []);

        // if there is transactions, remove the one that is older than 1 hour
        $last_hour_transactions = array_values(array_filter($last_hour_transactions, function ($transaction) {
            return $transaction->created_at > now()->subHour();
        }));

        // add the current transaction to the array
        $last_hour_transactions[] = $this->transaction;

        // store the cache for 1 hour
        cache()->put('last_hour_transactions', $last_hour_transactions, 60);
    }
}
