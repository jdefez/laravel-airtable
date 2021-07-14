<?php

namespace AxelDotDev\LaravelAirtable;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use stdClass;

class Airtable implements Airtableable
{
    public const GET = 'get';
    public const POST = 'post';
    public const PATCH = 'patch';
    public const DELETE = 'delete';

    //protected $app;

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
     * @var string
     */
    private string $uri;

    /**
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
    public function base(string $base, string $table): Airtableable
    {
        $this->base = $base;
        $this->table = rawurlencode($table);

        return $this;
    }

    public function setTable(string $table): Airtableable
    {
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
    public function all(string $view = 'Grid view', int $page_delay = 200000): Collection
    {
        $offset = false;
        $records = collect();

        do {
            $response = ! $offset
                ? $this->request(self::GET, '', compact('view'))
                : $this->request(self::GET, '', compact('view', 'offset'));

            $response = $response->collect();

            if (isset($response['records'])) {
                $records = $records->push(...$response['records']);
            }

            if (isset($response['offset'])) {
                $offset = $response['offset'];
                usleep($page_delay);
            } else {
                $offset = false;
            }
        } while ($offset);

        return $records->map(fn ($record) => (object) $record);
    }

    public function iterator(string $view = 'Grid view', int $page_delay = 200000)
    {
        $offset = false;

        do {
            $response = ! $offset
                ? $this->request(self::GET, '', compact('view'))
                : $this->request(self::GET, '', compact('view', 'offset'));

            $response = $response->collect();

            if (isset($response['records'])) {
                foreach ($response['records'] as $record) {
                    yield (object) $record;
                }
            }

            if (isset($response['offset'])) {
                $offset = $response['offset'];
                usleep($page_delay);
            } else {
                $offset = false;
            }
        } while ($offset);
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
        $response = $this->request(
            self::POST,
            '',
            ['records' => $data],
            ['Content-Type' => 'application/json']
        );

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
        $response = $this->request(
            self::PATCH,
            '',
            ['records' => $data],
            ['Content-Type' => 'application/json']
        );

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
    private function request(
        string $method,
        string $endpoint = '',
        array $data = [],
        array $headers = []
    ): Response {
        if (is_null($this->uri) || is_null($this->key)) {
            // todo: move elsewhere to be able to send some feed back on what's
            // happening here.
        }

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
