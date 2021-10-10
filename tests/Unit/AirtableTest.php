<?php

namespace AxelDotDev\LaravelAirtable\Tests;

use AxelDotDev\LaravelAirtable\Airtableable;
use AxelDotDev\LaravelAirtable\Facades\Airtable;
use Illuminate\Foundation\Testing\WithFaker;

class AirtableTest extends TestCase
{
    use WithFaker;

    public Airtableable $airtable;

    public const TABLE = 'Languages';

    public function setUp(): void
    {
        parent::setUp();

        $this->airtable = Airtable::base(env('AIRTABLE_BASE'))
            ->table('Languages');
    }

    /** @test */
    public function airtable_facade_is_instanciated()
    {
        $this->assertInstanceOf(Airtableable::class, $this->airtable);
    }

    /** @test */
    public function it_creates_records()
    {
        $this->markTestIncomplete('todo implement');
    }

    /** @test */
    public function get_method_returns_all_records()
    {
        $this->markTestIncomplete('todo implement');
        //$this->airtable->all();
    }

    /** @test */
    public function it_updates_records()
    {
        $this->markTestIncomplete('todo implement');
    }

    /** @test */
    public function it_deletes_records()
    {
        $this->markTestIncomplete('todo implement');
    }
}
