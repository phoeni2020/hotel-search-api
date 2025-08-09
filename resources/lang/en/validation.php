<?php

return [
    'location' => [
        'required' => 'The location field is required.',
        'string'   => 'The location must be a text value.',
    ],
    'check_in' => [
        'required'       => 'The check-in date is required.',
        'date'           => 'The check-in date must be a valid date.',
        'after_or_equal' => 'The check-in date must be today or later.',
    ],
    'check_out' => [
        'required' => 'The check-out date is required.',
        'date'     => 'The check-out date must be a valid date.',
        'after'    => 'The check-out date must be after the check-in date.',
    ],
    'guests' => [
        'integer' => 'The number of guests must be an integer.',
        'min'     => 'The number of guests must be at least 1.',
    ],
    'min_price' => [
        'numeric' => 'The minimum price must be a number.',
        'min'     => 'The minimum price must be at least 0.',
    ],
    'max_price' => [
        'numeric' => 'The maximum price must be a number.',
        'gte'     => 'The maximum price must be greater than or equal to the minimum price.',
    ],
];
