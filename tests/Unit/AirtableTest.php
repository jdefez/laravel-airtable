<?php

namespace AxelDotDev\LaravelAirtable\Tests;

use AxelDotDev\LaravelAirtable\Airtableable;
use AxelDotDev\LaravelAirtable\Facades\Airtable;

class AirtableTest extends TestCase
{
    const AIRTABLE_BASE = 'app9f4tecQj3wht5U';

    public Airtableable $airtable;

    public function setUp(): void
    {
        parent::setUp();

        $this->airtable = Airtable::base(self::AIRTABLE_BASE, 'Languages');
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
        dd($records);
        $this->assertNotEmpty($records);
    }
}
