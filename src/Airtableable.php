<?php

namespace AxelDotDev\LaravelAirtable;

use Illuminate\Support\Collection;
use Generator;
use stdClass;

interface Airtableable
{
    public function table(string $table): Airtableable;

    public function view(string $view): Airtableable;

    public function update(array $data): Collection;

    public function create(array $data): Collection;

    public function delete(array $data): Collection;

    public function base(string $base): Airtableable;

    public function find(string $id): stdClass;

    public function all(): Collection;

    public function iterator(): Generator;
}
