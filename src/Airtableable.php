<?php

namespace AxelDotDev\LaravelAirtable;

use Illuminate\Support\Collection;
use Generator;
use stdClass;

interface Airtableable
{
    public function table(string $table): Airtableable;

    public function view(string $view): Airtableable;

    public function update(array $records): Generator;

    public function create(array $records): Generator;

    public function delete(array $records): Generator;

    public function base(string $base): Airtableable;

    public function find(string $id): stdClass;

    public function all(): Collection;

    public function getIterator(): Generator;

    public function maxRecords(int $size): self;

    public function pageSize(int $size): self;

    public function fields(array $fields): self;

    public function sortBy(string $field, ?string $direction = 'asc'): self;
}
