<?php

namespace AxelDotDev\LaravelAirtable\Tests;

use AxelDotDev\LaravelAirtable\Facades\Airtable;
use AxelDotDev\LaravelAirtable\Airtableable;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * To run this tests you have to:
 *
 *  1. Create your own aritable table named 'Test'.
 *   It should have:
 *     - a column named 'Name'
 *     - and it s view name should be 'Grid view'
 *
 *  2. Create and Configure ./phpunit.xml file. It should contain:
 *
 * <coverage>
 *   <include>
 *     <directory suffix=".php">src/</directory>
 *   </include>
 * </coverage>
 * <testsuites>
 *   <testsuite name="Unit">
 *     <directory suffix="Test.php">tests/Unit</directory>
 *   </testsuite>
 *   <testsuite name="Feature">
 *     <directory suffix="Test.php">tests/Feature</directory>
 *   </testsuite>
 * </testsuites>
 * <php>
 *   <env name="AIRTABLE_BASE" value="xxxxxxxxxxxxxxxxx"/>
 *   <env name="AIRTABLE_KEY" value="xxxxxxxxxxxxxxxxxx"/>
 *   <env name="APP_KEY" value="xxxxxxxxxxxxxxxxxxxxxxx"/>
 * </php>
 */

class AirtableTest extends TestCase
{
    use WithFaker;

    public Airtableable $airtable;

    public const TABLE = 'Test';

    public function setUp(): void
    {
        parent::setUp();

        $this->airtable = Airtable::base(env('AIRTABLE_BASE'));
    }

    /** @test */
    public function airtable_facade_is_instanciated()
    {
        $this->assertInstanceOf(Airtableable::class, $this->airtable);
    }

    /** @test */
    public function it_creates_records()
    {
        $howmany = 15;

        // creating records
        $created = collect();
        $count = 1;
        collect($this->faker->words($howmany))
            ->map(function ($item) use (&$count) {
                $data = ['fields' => ['Name' => $item . $count]];
                $count++;
                return $data;
            })
            ->chunk(10)
            ->each(function ($chunk) use (&$created) {
                $records = $this->airtable
                    ->table(self::TABLE)
                    ->create($chunk->values()->toArray());
                $created = $created->merge($records);
            });

        $this->assertCount($howmany, $created->toArray());
    }

    /** @test */
    public function get_method_returns_all_records()
    {
        $records = $this->airtable->table(self::TABLE)->all();
        $this->assertNotEmpty($records);
    }

    /** @test */
    public function it_updates_records()
    {
        $records = collect();
        $iterator = $this->airtable->table(self::TABLE)->iterator();
        foreach ($iterator as $record) {
            $records->push($record);
        }

        // updating records
        $updated = collect();
        $records->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'fields' => [
                    'Name' => $item->fields->Name . ' updated',
                ]
            ];
        })->chunk(10)
            ->each(function ($chunk) use (&$updated) {
                $records = $this->airtable
                    ->table(self::TABLE)
                    ->update($chunk->values()->toArray());
                $updated = $updated->merge($records);
            });

        $this->assertCount($records->count(), $updated->toArray());
    }

    /** @test */
    public function it_deletes_records()
    {
        $records = collect();
        $iterator = $this->airtable->table(self::TABLE)->iterator();
        foreach ($iterator as $record) {
            $records->push($record->id);
        }

        $deleted = collect();
        $records->chunk(10)
            ->each(
                function ($chunk) use (&$deleted) {
                    $records = $this->airtable->delete($chunk->values()->toArray());
                    $deleted = $deleted->merge($records);
                }
            );
        $this->assertCount($records->count(), $deleted);
    }
}
