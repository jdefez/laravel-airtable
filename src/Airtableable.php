<?php

namespace AxelDotDev\LaravelAirtable;

use Illuminate\Support\Collection;
use Generator;
use stdClass;

interface Airtableable
{
    public function table(string $table): Airtableable;

    public function view(string $view): Airtableable;

    public function update(array $data): Generator;

    public function create(array $data): Generator;

    public function delete(array $data): Generator;

    public function base(string $base): Airtableable;

    public function find(string $id): stdClass;

    public function all(): Collection;

    public function iterator(): Generator;
}
