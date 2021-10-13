<?php

namespace AxelDotDev\LaravelAirtable\Tests;

use AxelDotDev\LaravelAirtable\Airtableable;
use AxelDotDev\LaravelAirtable\Facades\Airtable;
use Generator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AirtableTest extends TestCase
{
    use WithFaker;

    public Airtableable $airtable;

    public const TABLE = 'Languages';

    public function setUp(): void
    {
        parent::setUp();

        $this->airtable = Airtable::base(env('AIRTABLE_BASE'))
            ->table('airtable_database_name');
    }

    /** @test */
    public function airtable_facade_is_instanciated()
    {
        $this->assertInstanceOf(Airtableable::class, $this->airtable);
    }

    /** @test */
    public function all_method_returns_all_records()
    {
        [$responseOne, $responseTwo] = $this->loadStub('paginated-response.php');

        Http::fake([
            'https://api.airtable.com/v0/*' => Http::sequence()
                ->push($responseOne, 200)
                ->push($responseTwo, 200)
        ]);

        $records = $this->airtable->all();

        $this->assertInstanceOf(Collection::class, $records);
        $this->assertCount(4, $records);
    }

    /** @test */
    public function getIterator_method_returns_a_generator()
    {
        [$responseOne, $responseTwo] = $this->loadStub('paginated-response.php');

        Http::fake([
            'https://api.airtable.com/v0/*' => Http::sequence()
                ->push($responseOne, 200)
                ->push($responseTwo, 200)
        ]);

        $generator = $this->airtable->getIterator();

        $this->assertInstanceOf(Generator::class, $generator);

        $count = 0;
        foreach ($generator as $row) {
            $count++;
        }

        $this->assertEquals(4, $count);
    }

    /** @test */
    public function find_method_returns_an_object()
    {
        Http::fake([
            'https://api.airtable.com/v0/*' => Http::response([
                'id' => 'recAyN0HWcTUGCb08',
                'fields' => [
                    'Name' => 'Afrikaans',
                    'Code' => 'af-ZA',
                    'Country' => [
                        'recHulmhDWtCtQGdA'
                    ]
                ],
                'createdTime' => '2021-07-11T21:37:59.000Z'
            ], 200),
        ]);

        $record = $this->airtable->find('recEf2Dhv3QhJl7ng');

        $this->assertIsObject($record);
    }

    /** @test */
    public function create_method_creates_one_record_and_returns_generator()
    {
        $records = $this->generateRecords(1);

        Http::fake([
            'https://api.airtable.com/v0/*' => Http::response([
                'records' => $records
            ], 200)
        ]);

        $generator = $this->airtable->create($records);

        $results = [];
        foreach ($generator as $record) {
            $results[] = [
                'id' => $record->id,
                'name' => $record->fields->Name,
                'code' => $record->fields->Code,
            ];
        }

        $this->assertEquals($this->getExpectedResultsFromRecords($records), $results);
        $this->assertCount(count($records), $results);
    }

    /** @test */
    public function when_create_method_is_called_with_more_than_10_records_it_returns_a_generator()
    {
        $records = $this->generateRecords(12);
        $responseRecords = collect($records)
            ->chunk(10)
            ->toArray();

        Http::fake([
            'https://api.airtable.com/v0/*' => Http::sequence()
                ->push(['records' => $responseRecords[0]], 200)
                ->push(['records' => $responseRecords[1]], 200)
        ]);

        $generator = $this->airtable->create($records);

        $results = [];
        foreach ($generator as $record) {
            $results[] = [
                'id' => $record->id,
                'name' => $record->fields->Name,
                'code' => $record->fields->Code,
            ];
        }

        $this->assertEquals($this->getExpectedResultsFromRecords($records), $results);
        $this->assertCount(count($records), $results);
    }

    /** @test */
    public function when_update_method_is_called_it_yields_results()
    {
        $records = $this->generateUpdateRecords(12);
        $responseRecords = collect($records)
            ->chunk(10)
            ->toArray();

        Http::fake([
            'https://api.airtable.com/v0/*' => Http::sequence()
                ->push(['records' => $responseRecords[0]], 200)
                ->push(['records' => $responseRecords[1]], 200)
        ]);

        $generator = $this->airtable->update($records);

        $results = [];
        foreach ($generator as $record) {
            $results[] = [
                'id' => $record->id,
                'name' => $record->fields->Name,
                'code' => $record->fields->Code,
            ];
        }

        $this->assertEquals($this->getExpectedResultsFromRecords($records), $results);
        $this->assertCount(count($records), $results);
    }

    /** @test */
    public function when_delete_method_is_called_it_yields_deleted_results()
    {
        $records = $this->generateDeleteRecords(4);
        $responseRecords = collect($records)
            ->map(fn ($item) => [
                (object) [
                    'id' => $item,
                    'deleted' => true,
                ]])
            ->toArray();

        Http::fake([
            'https://api.airtable.com/v0/*' => Http::sequence()
                ->push(['records' => $responseRecords[0]], 200)
                ->push(['records' => $responseRecords[1]], 200)
                ->push(['records' => $responseRecords[2]], 200)
                ->push(['records' => $responseRecords[3]], 200)
        ]);

        $generator = $this->airtable->delete($records);

        $results = [];
        foreach ($generator as $record) {
            $results[] = [
                'id' => $record->id,
            ];
        }

        $this->assertEquals(
            $this->getExpectedResultsFromDeletedRecords($records),
            $results
        );
        $this->assertCount(count($records), $results);
    }

    protected function loadStub(string $filename): array
    {
        $path = __DIR__ . '/../Stubs/' . $filename;

        if (!file_exists($path)) {
            dd(sprintf('%s file not found', $path));
        }

        return require $path;
    }

    protected function getExpectedResultsFromRecords($records)
    {
        return array_map(fn ($item) => [
            'id' => $item['id'],
            'code' => $item['fields']['Code'],
            'name' => $item['fields']['Name'],
        ], $records);
    }

    protected function generateUpdateRecords(int $count): array
    {
        $return = array_fill(0, $count, null);

        return array_map(
            fn ($item) => $this->generateRecord(),
            $return
        );
    }

    protected function generateRecords(int $count): array
    {
        $return = array_fill(0, $count, null);

        return array_map(
            fn ($item) => $this->generateRecord(),
            $return
        );
    }

    protected function generateRecord(): array
    {
        return [
            'id' => $this->generateId(),
            'fields' => [
                'Name' => $this->faker->country(),
                'Code' => $this->faker->regexify('[a-z]{2}_[A-Z]{2}'),
                'Country' => [
                    $this->generateId()
                ]
            ]
        ];
    }

    protected function generateId()
    {
        return $this->faker->regexify('rec[A-Za-z0-9]{14}');
    }

    private function generateDeleteRecords(int $count)
    {
        $return = array_fill(0, $count, null);

        return array_map(
            fn ($item) => $this->generateId(),
            $return
        );
    }

    private function getExpectedResultsFromDeletedRecords(array $records)
    {
        return array_map(
            fn ($item) => [
                'id' => $item,
            ],
            $records
        );
    }
}
