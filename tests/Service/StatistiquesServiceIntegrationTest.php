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

        // Vide la mémoire de Doctrine : find() doit relire MongoDB. Sinon, il renverrait
        // l'objet encore en mémoire, et le test passerait même si rien n'avait été écrit en base.
        $this->documentManager->clear();
        $relue = $this->documentManager->find(Consultation::class, $consultation->getId());

        $this->assertNotNull($relue);
        $this->assertSame(12, $relue->getRessourceId());
        $this->assertSame('Doctrine ODM', $relue->getTitre());
        $this->assertSame('Bases de données', $relue->getCategorie());
    }

    public function testTopRessourcesClasseParNombreEtIgnoreLesConsultationsAnciennes(): void
    {
        $this->consulter(1, 'Doctrine ODM', '2026-09-20', 2);
        $this->consulter(2, 'Docker Compose', '2026-09-21', 3);
        $this->consulter(3, 'PHPUnit', '2026-09-22', 1);
        $this->consulter(3, 'PHPUnit', '2026-08-01', 5);
        $this->documentManager->flush();

        $top = $this->service->topRessources(new \DateTimeImmutable('2026-09-16'));

        $this->assertSame([2, 1, 3], array_column($top, '_id'));
        $this->assertSame([3, 2, 1], array_column($top, 'nombre'));
    }

    private function consulter(int $ressourceId, string $titre, string $date, int $fois): void
    {
        for ($i = 0; $i < $fois; $i++) {
            $this->documentManager->persist(
                new Consultation($ressourceId, $titre, 'Symfony & PHP', new \DateTimeImmutable($date))
            );
        }
    }
}