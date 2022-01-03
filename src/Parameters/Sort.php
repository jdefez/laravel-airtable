<?php

namespace AxelDotDev\LaravelAirtable\Parameters;

class Sort
{
    // returned
    // sort[0][field]=Name
    // sort[0][direction]=desc

    public string $field;

    public string $direction = 'asc';

    public function __construct(string $field, ?string $direction)
    {
        if (! $direction ||
            ! in_array($direction, ['desc', 'asc'])
        ) {
            $direction = 'asc';
        }

        $this->direction = $direction;
        $this->field = $field;
    }
}
