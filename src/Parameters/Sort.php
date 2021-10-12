<?php

namespace AxelDotDev\LaravelAirtable\Parameters;

use InvalidArgumentException;

class Sort
{
    // returned
    // sort[0][field]=Name
    // sort[0][direction]=desc

    public string $field;

    public string $direction;

    public function __construct(string $field, string $direction = 'asc')
    {
        if ( ! in_array($direction, ['desc', 'asc'])) {
            throw new InvalidArgumentException('direction must be `desc` or `asc`');
        }

        $this->direction = $direction;
        $this->field = $field;
    }
}
