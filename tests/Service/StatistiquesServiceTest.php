<?php

namespace App\Tests\Service;

use App\Service\StatistiquesService;
use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\TestCase;

class StatistiquesServiceTest extends TestCase
{
    public function testDebutPeriodeRemonteAuDebutDuJour(): void
    {
        $service = new StatistiquesService($this->createStub(DocumentManager::class));
        $reference = new \DateTimeImmutable('2026-09-23 15:30:00');

        $debut = $service->debutPeriode(7, $reference);

        $this->assertEquals(new \DateTimeImmutable('2026-09-16 00:00:00'), $debut);
    }
}