<?php

namespace AxelDotDev\LaravelAirtable;

use Illuminate\Support\Collection;
use stdClass;

interface Airtableable
{
    public function base(string $base, string $table): Airtableable;

    public function setTable(string $table): Airtableable;

    public function all(string $view = 'Grid view', int $page_delay = 200000): Collection;

    public function find(string $id): stdClass;

    public function create(array $data): Collection;

    public function update(array $data): Collection;

    public function delete(array $data): Collection;
}
