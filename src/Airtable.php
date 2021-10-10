<?php

namespace AxelDotDev\LaravelAirtable;

use AxelDotDev\LaravelAirtable\Parameters\Parameters;
use Generator;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use stdClass;

class Airtable implements Airtableable
{
    public const GET = 'get';

    public const POST = 'post';

    public const PATCH = 'patch';

    public const DELETE = 'delete';

    /**
     * The Airtable base Id
     *
     * @var string
     */
    protected string $base;

    /**
     * The Airtable table name
     *
     * @var string
     */
    protected string $table;

    /**
     * The Airtable table view name
     *
     * @var string
     */
    protected string $view = 'Grid view';

    /**
     * Delay in microseconds before calling the next records page
     *
     * @var int
     */
    protected int $page_delay = 200000;

    /**
     * Airtable api url
     *
     * @var string
     */
    private string $uri;

    /**
     * Airtable api key
     *
     * @var string
     */
    private string $key;

    public function __construct(string $uri, string $key)
    {
        $this->parameters = new Parameters();
        $this->uri = ! Str::of($uri)->endsWith('/') ? $uri . '/' : $uri;
        $this->key = $key;
    }

    /**
     * Define the base and the table
     *
     * @param string $base
     * @param string $table
     *
     * @return LaravelAirtable
     */
    public function base(string $base): self
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Set the current Airtable table name
     *
     * @param string $table
     *
     * @return Airtableable
     */
    public function table(string $table): self
    {
        $this->table = rawurlencode($table);

        return $this;
    }

    /**
     * Set the Airtable table view name
     *
     * @param string $view
     *
     * @return Airtableable
     */
    public function view(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Set the time in microseconds to wait between two requests
     *
     * @param int $delay
     *
     * @return Airtableable
     */
    public function pageDelay(int $delay): self
    {
        $this->page_delay = $delay;

        return $this;
    }

    /**
     * List all records
     *
     * @return Collection
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
     * @return Generator
     *
     * @throws BindingResolutionException
     */
    public function iterator(): Generator
    {
        foreach ($this->walk() as $record) {
            yield $record;
        }
    }

    /**
     * Find one record
     *
     * @param string $id
     *
     * @return stdClass
     *
     * @throws BindingResolutionException
     */
    public function find(string $id): stdClass
    {
        return (object) $this->request(self::GET, '/' . $id)->object();
    }

    /**
     * Create one or many records
     *
     * @param array $records up to ten records
     *
     * @return Generator
     *
     * @throws BindingResolutionException
     */
    public function create(array $records): Generator
    {
        $response = $this->request(self::POST, '', compact('records'))
            ->object();

        foreach ($response->records as $records) {
            yield (object) $records;
        }
    }

    /**
     * Update one or many records
     *
     * @param array $records up to 10 records
     *
     * @return Generator
     *
     * @throws BindingResolutionException
     */
    public function update(array $records): Generator
    {
        $response = $this->request(self::PATCH, '', compact('records'))
            ->object();

        foreach ($response->records as $records) {
            yield (object) $records;
        }
    }

    /**
     * Delete one or many records
     *
     * @param array $records
     *
     * @return Generator
     *
     * @throws BindingResolutionException
     */
    public function delete(array $records): Generator
    {
        foreach ($records as $id) {
            yield $this->request(self::DELETE, '/' . $id)->object();
        }
    }

    /**
     * Walk over Airtable paginated responses and yield records
     *
     * @return Generator
     *
     * @throws BindingResolutionException
     */
    private function walk(): Generator
    {
        $offset = false;
        $view = $this->view;

        do {
            $response = ! $offset
                ? $this->request(self::GET, '', compact('view'))
                : $this->request(self::GET, '', compact('view', 'offset'));

            $response = $response->object();

            if (isset($response->records)) {
                foreach ($response->records as $record) {
                    yield (object) $record;
                }
            }

            if (property_exists($response, 'offset')) {
                $offset = $response->offset;
                usleep($this->page_delay);
            } else {
                $offset = false;
            }
        } while ($offset);
    }

    /**
     * Make the API request
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $data
     *
     * @return Response
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
}
