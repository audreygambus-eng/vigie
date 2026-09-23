<?php

namespace App\Tests\Service;

use App\Document\Consultation;
use App\Service\StatistiquesService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StatistiquesServiceIntegrationTest extends KernelTestCase
{
    private DocumentManager $documentManager;
    private StatistiquesService $service;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var DocumentManager $documentManager */
        $documentManager = static::getContainer()->get('doctrine_mongodb')->getManager();
        $this->documentManager = $documentManager;

        $this->documentManager->getDocumentCollection(Consultation::class)->deleteMany([]);
        $this->documentManager->getSchemaManager()->ensureDocumentIndexes(Consultation::class);

        $this->service = new StatistiquesService($this->documentManager);
    }

    public function testEnregistrerConsultationPersisteLeDocument(): void
    {
        $consultation = $this->service->enregistrerConsultation(12, 'Doctrine ODM', 'Bases de données');

        $this->documentManager->clear();
        $relue = $this->documentManager->find(Consultation::class, $consultation->getId());

        $this->assertNotNull($relue);
        $this->assertSame(12, $relue->getRessourceId());
        $this->assertSame('Doctrine ODM', $relue->getTitre());
        $this->assertSame('Bases de données', $relue->getCategorie());
    }
}