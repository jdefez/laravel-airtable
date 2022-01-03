<?php

namespace AxelDotDev\LaravelAirtable\Records;

class Record
{
    public function __construct(public object $data)
    {
    }

    public function isEmpty(): bool
    {
        // todo: implement
    }

    public function toArray(): array
    {
        $return = [];

        if ($this->has('id')) {
            $return['id'] = $this->data->id;
        }

        if ($this->has('fields')) {
            foreach ($this->data->fields as $key => $value) {
                $return[trim($key)] = $value;
            }
        }

        return $return;
    }

    public function toObject(): object
    {
        return (object) $this->toArray();
    }

    private function has(string $key): bool
    {
        return property_exists($this->data, $key);
    }
}
