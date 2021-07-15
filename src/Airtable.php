<?php

namespace AxelDotDev\LaravelAirtable;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Generator;
use stdClass;

class Airtable implements Airtableable
{
    // todo:
    //   - create, update or delete method should not store anything. This could
    //   be done by yielding response results

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
        $this->uri = $uri;
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
    public function base(string $base): Airtableable
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
    public function table(string $table): Airtableable
    {
        $this->table = rawurlencode($table);

        return $this;
    }

    /**
     * Set the Airtable table view name
     *
     * @param string $view
     * @return Airtableable
     */
    public function view(string $view): Airtableable
    {
        $this->view = $view;

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

        $iterator = $this->walk();
        foreach ($iterator as $record) {
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
        $iterator = $this->walk(fn ($record) => $record);
        foreach ($iterator as $record) {
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
        return (object) $this->request(self::GET, '/' . $id)->json();
    }

    /**
     * Create one or many records
     *
     * @param array $records up to ten records
     *
     * @return Collection
     *
     * @throws BindingResolutionException
     */
    public function create(array $records): Collection
    {
        $response = $this->request(
            self::POST,
            '',
            compact('records'),
            ['Content-Type' => 'application/json']
        )->object();

        return collect($response->records)
            ->map(fn ($record) => (object) $record);
    }

    /**
     * Update one or many records
     *
     * @param array $records up to 10 records
     *
     * @return Collection
     *
     * @throws BindingResolutionException
     */
    public function update(array $records): Collection
    {
        $response = $this->request(
            self::PATCH,
            '',
            compact('records'),
            ['Content-Type' => 'application/json']
        )->object();

        return collect($response->records)
            ->map(fn ($record) => (object) $record);
    }

    /**
     * Delete one or many records
     *
     * @param array $records
     *
     * @return Collection
     *
     * @throws BindingResolutionException
     */
    public function delete(array $records): Collection
    {
        // Note: batch deleting on Airtable API is simply not working we have
        //  to loop over each records

        $deleted = collect();
        foreach ($records as $id) {
            $response = $this->request(self::DELETE, '/' . $id)->object();

            $deleted->push($response->id);
        }

        return $deleted;
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
     * @param array  $headers
     *
     * @return Response
     *
     * @throws BindingResolutionException
     */
    private function request(
        string $method,
        string $endpoint = '',
        array $data = [],
        array $headers = []
    ): Response {
        $response = Http::withToken($this->key);

        if (! empty($headers)) {
            $response = $response->withHeaders($headers);
        }

        $response = $response->$method(
            $this->uri . $this->base . '/' . $this->table . $endpoint,
            $data
        );

        $response->throw();

        return $response;
    }
}
