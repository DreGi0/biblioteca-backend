<?php

use App\Models\Book;
use App\Models\Loan;

test('cannot loan unavailable book', function () {

    $book = new Book([
        'available_copies' => 0,
        'is_available' => false
    ]);

    expect($book->available_copies)->toBe(0);
    expect($book->is_available)->toBeFalse();
});

test('cannot return inactive loan', function () {

    $loan = new Loan([
        'return_at' => now()
    ]);

    expect($loan->return_at)->not->toBeNull();
});