<?php

namespace App\Observers;

use App\Jobs\TransactionAlert;
use App\Models\Transaction;

class TransactionObserver
{

    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        TransactionAlert::dispatch($transaction);
    }
}
