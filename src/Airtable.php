<?php

namespace AxelDotDev\LaravelAirtable;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use stdClass;

class Airtable
{
    const GET = 'get';
    const POST = 'post';
    const PATCH = 'patch';
    const DELETE = 'delete';

    protected $app;

    /**
     * The Airtable base
     *
     * @var string
     */
    protected string $base;

    /**
     * The airtable base table
     *
     * @var string
     */
    protected string $table;

    /**
     * Construct object
     *
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Define the base and the table
     *
     * @param string $base
     * @param string $table
     *
     * @return LaravelAirtable
     */
    public function base(string $base, string $table): self
    {
        $this->base = $base;
        $this->table = rawurlencode($table);

        return $this;
    }

    /**
     * List all records
     *
     * @return Collection
     *
     * @throws BindingResolutionException
     */
    public function all(int $page_delay = 200000): Collection
    {
        $offset = null;
        $records = collect();

        do {
            $response = is_null($offset)
                ? $this->request(self::GET, '?view=Grid%20view')
                : $this->request(self::GET, '?offset=' . $offset . 'view=Grid%20view');

            $response = $response->collect();

            if (isset($response['records'])) {
                $records = $records->merge($response['records']);
            }

            if (isset($response['offset'])) {
                $offset = $response['offset'];
                usleep($page_delay);
            } else {
                $offset = null;
            }
        } while ($offset);

        return $records->map(fn ($record) => (object) $record);
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
        $response = $this->request(self::GET, '/' . $id);

        return (object) $response->json();
    }

    /**
     * Create one or many records
     *
     * @param array $data
     *
     * @return Collection
     *
     * @throws BindingResolutionException
     */
    public function create(array $data): Collection
    {
        $response = $this->request(self::POST, '', ['records' => $data], ['Content-Type' => 'application/json']);

        $records = collect($response->collect()['records']);

        return $records->map(fn ($record) => (object) $record);
    }

    /**
     * Update one or many records
     *
     * @param array $data
     *
     * @return Collection
     *
     * @throws BindingResolutionException
     */
    public function update(array $data): Collection
    {
        $response = $this->request(self::PATCH, '', ['records' => $data], ['Content-Type' => 'application/json']);

        $records = collect($response->collect()['records']);

        return $records->map(fn ($record) => (object) $record);
    }

    /**
     * Delete one or many records
     *
     * @param array $data
     *
     * @return Collection
     *
     * @throws BindingResolutionException
     */
    public function delete(array $data): Collection
    {
        $response = $this->request(self::DELETE, '', ['records' => $data]);

        $records = collect($response->collect()['records']);

        return $records->map(fn ($record) => (object) $record);
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
    private function request(string $method, string $endpoint = '', array $data = [], array $headers = []): Response
    {
        $uri = $this->app['config']['laravel-airtable.uri'];
        $key = $this->app['config']['laravel-airtable.key'];

        if (is_null($uri) || is_null($key)) {
        }

        $response = Http::withToken($key);

        if (! empty($headers)) {
            $response = $response->withHeaders($headers);
        }

        $response = $response->$method($uri . $this->base . '/' . $this->table . $endpoint, $data);

        $response->throw();

        return $response;
    }
}
