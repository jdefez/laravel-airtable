<?php

namespace AxelDotDev\LaravelAirtable;

use AxelDotDev\LaravelAirtable\Parameters\Parameters;
use Generator;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use stdClass;

class Airtable implements Airtableable
{
    public const MAX_RECORDS = 10;

    public const GET = 'get';

    public const POST = 'post';

    public const PATCH = 'patch';

    public const DELETE = 'delete';

    /**
     * The Airtable base Id
     */
    protected string $base;

    /**
     * The Airtable table name
     */
    protected string $table;

    /**
     * Delay in microseconds before calling the next records page
     */
    protected int $page_delay = 200000;

    /**
     * Airtable api url
     */
    private string $uri;

    /**
     * Airtable api key
     */
    private string $key;

    private Parameters $parameters;

    public function __construct(string $uri, string $key)
    {
        $this->parameters = new Parameters();
        $this->parameters->view = 'Grid view';

        $this->uri = ! Str::of($uri)->endsWith('/') ? $uri . '/' : $uri;
        $this->key = $key;
    }

    /**
     * Define the base and the table
     */
    public function base(string $base): self
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Set the current Airtable table name
     */
    public function table(string $table): self
    {
        $this->table = rawurlencode($table);

        return $this;
    }

    /**
     * Set the Airtable table view name
     */
    public function view(string $view): self
    {
        $this->parameters->view = $view;

        return $this;
    }

    /**
     * Set the time in microseconds to wait between two requests
     */
    public function pageDelay(int $delay): self
    {
        $this->page_delay = $delay;

        return $this;
    }

    /**
     * List all records
     *
     * @throws BindingResolutionException
     */
    public function all(): Collection
    {
        $records = collect();

        foreach ($this->walk() as $record) {
            $records->push($record);
        }

        return $records;
    }

    /**
     * Get an iterator of all the table records
     *
     * @throws BindingResolutionException
     */
    public function getIterator(): Generator
    {
        foreach ($this->walk() as $record) {
            yield $record;
        }
    }

    /**
     * Find one record
     *
     * @throws BindingResolutionException
     */
    public function find(string $id): stdClass
    {
        return $this->request(self::GET, '/' . $id)
            ->object();
    }

    /**
     * Create one or many records. Each record fields is contained in a
     * "fields" attribute
     *
     * @throws BindingResolutionException
     */
    public function create(array $records): Generator
    {
        foreach ($this->chunckRecords($records) as $records) {
            $response = $this->request(self::POST, '', compact('records'))
                ->object();

            foreach ($response->records as $records) {
                yield (object) $records;
            }
        }
    }

    /**
     * Update one or many records. Each record fields is contained in a
     * "fields" attribute
     *
     * @throws BindingResolutionException
     */
    public function update(array $records): Generator
    {
        foreach ($this->chunckRecords($records) as $records) {
            $response = $this->request(self::PATCH, '', compact('records'))
                ->object();

            foreach ($response->records as $records) {
                yield (object) $records;
            }
        }
    }

    /**
     * Delete one or many records
     *
     * @throws BindingResolutionException
     */
    public function delete(array $records): Generator
    {
        foreach ($records as $id) {
            $response = $this->request(self::DELETE, '/' . $id)->object();

            yield $response->records[0];
        }
    }

    public function maxRecords(int $size): self
    {
        $this->parameters->setMaxRecords($size);

        return $this;
    }

    public function pageSize(int $size): self
    {
        $this->parameters->setPageSize($size);

        return $this;
    }

    public function fields(array $fields): self
    {
        $this->parameters->setFields($fields);

        return $this;
    }

    public function sortBy(string $field, ?string $direction = 'asc'): self
    {
        $this->parameters->setSort($field, $direction);

        return $this;
    }

    /**
     * Walk over Airtable paginated responses and yield records
     *
     * @throws BindingResolutionException
     */
    private function walk(): Generator
    {
        $this->parameters->offset = false;

        do {
            $response = $this->request(self::GET, '', $this->parameters->toArray())
                ->object();

            if (isset($response->records)) {
                foreach ($response->records as $record) {
                    yield (object) $record;
                }
            }

            if (property_exists($response, 'offset')) {
                $this->parameters->setOffset($response->offset);
                usleep($this->page_delay);
            } else {
                $this->parameters->setOffset(false);
            }
        } while ($this->parameters->offset);
    }

    /**
     * Make the API request
     *
     * @throws BindingResolutionException
     */
    private function request(
        string $method,
        string $endpoint = '',
        array $data = [],
    ): Response {
        return Http::acceptJson()
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withToken($this->key)
            ->{$method}($this->getUri($endpoint), $data)
            ->throw();
    }

    private function getUri(string $endpoint)
    {
        return $this->uri . $this->base . '/' . $this->table . $endpoint;
    }

    private function chunckRecords(array $records): Collection
    {
        return collect($records)
            ->map(fn ($item) => $this->setFieldsAttribute($item))
            ->chunk(self::MAX_RECORDS);
    }

    private function setFieldsAttribute($item): array
    {
        if (!array_key_exists('fields', $item)) {
            $return = [
                'fields' =>  Arr::except($item, ['id']),
            ];

            if (array_key_exists('id', $item)) {
                $return['id'] = $item['id'];
            }

            return $return;
        }

        return $item;
    }
}
