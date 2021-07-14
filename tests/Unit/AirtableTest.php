<?php

namespace AxelDotDev\LaravelAirtable\Tests;

use AxelDotDev\LaravelAirtable\Facades\Airtable;
use AxelDotDev\LaravelAirtable\Airtableable;

class AirtableTest extends TestCase
{
    public Airtableable $airtable;

    public function setUp(): void
    {
        parent::setUp();

        $this->airtable = Airtable::base(env('AIRTABLE_BASE'), 'Languages');
    }

    /** @test */
    public function airtable_facade_is_instanciated()
    {
        $this->assertInstanceOf(Airtableable::class, $this->airtable);
    }

    /** @test */
    public function get_method_returns_all_records()
    {
        $records = $this->airtable->all();
        $this->assertNotEmpty($records);
    }

    /** @test */
    public function iterator_method_returns_all_records()
    {
        $records = [];
        foreach ($this->airtable->iterator() as $record) {
            $records[] = $record->id;
        }
        $this->assertNotEmpty($records);
    }
}
