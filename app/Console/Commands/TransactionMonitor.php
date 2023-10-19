<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TransactionMonitor extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:transaction-monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor transactions for suspicious activity.';

    /**
     * Execute the console command.
     */
    public function handle() {
        // get last 24 hours hourly average transaction count
        $average_transaction_count = cache()->remember( 'average_transaction_count', 60, function () {
            return Transaction::where( 'created_at', '>', now()->subHours( 24 ) )->count() / 24;
        } );

        // get last hour transactions count from cache
        $last_hour_transactions       = cache()->get( 'last_hour_transactions', [] );
        $last_hour_transactions_count = count( $last_hour_transactions );

        // if the last hour transactions count is greater than the average
        // send an email to the manager
        if ( $last_hour_transactions_count > $average_transaction_count ) {

            // check the percentage of the increase
            $increase_percentage = ( $last_hour_transactions_count - $average_transaction_count ) / $average_transaction_count * 100;

            $threshold_percentage = config( 'transaction.threshold_percentage', 10 );

            if ( $increase_percentage > $threshold_percentage ) {
                // Send an email/notification to the manager
                // $this->transaction->syndicate->manager->notify(new TransactionNotification());
                // logging for demo purposes
                info( 'Transaction count exceeds threshold', ['transaction_count' => $last_hour_transactions_count] );
            }
        }
    }
}
