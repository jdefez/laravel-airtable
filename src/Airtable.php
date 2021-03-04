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
    public function all(): Collection
    {
        $response = $this->request(self::GET, '?maxRecords=500&view=Grid%20view');

        return $response['records']->collect()->map(fn ($record) => (object) $record);
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

        return $response['records']->collect()->map(fn ($record) => (object) $record);
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

        return $response['records']->collect()->map(fn ($record) => (object) $record);
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

        return $response['records']->collect()->map(fn ($record) => (object) $record);
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
