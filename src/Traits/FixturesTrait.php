<?php

namespace App\Traits;

use Hautelook\AliceBundle\PhpUnit\FixtureStore;

trait FixturesTrait
{
    protected $all_fixtures;

    public function loadFixturesTrait(): void
    {
        $this->all_fixtures = FixtureStore::getFixtures();
    }
}
