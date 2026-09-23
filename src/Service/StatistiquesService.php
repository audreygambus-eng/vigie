<?php

namespace App\Service;

use App\Document\Consultation;
use Doctrine\ODM\MongoDB\DocumentManager;

class StatistiquesService
{
    public function __construct(
        private readonly DocumentManager $documentManager,
    ) {
    }

    public function enregistrerConsultation(int $ressourceId, string $titre, string $categorie): Consultation
    {
        $consultation = new Consultation($ressourceId, $titre, $categorie);

        $this->documentManager->persist($consultation);
        $this->documentManager->flush();

        return $consultation;
    }

        public function debutPeriode(int $jours, ?\DateTimeImmutable $reference = null): \DateTimeImmutable
    {
        if ($jours < 1) {
            throw new \InvalidArgumentException('La période doit compter au moins un jour.');
        }

        $reference ??= new \DateTimeImmutable();

        return $reference->modify(sprintf('-%d days', $jours))->setTime(0, 0);
    }

        public function topRessources(\DateTimeImmutable $depuis, int $limite = 10): array
    {
        $builder = $this->documentManager->createAggregationBuilder(Consultation::class);

        $builder
            ->match()
                ->field('dateConsultation')->gte($depuis)
            ->group()
                ->field('id')->expression('$ressourceId')
                ->field('titre')->first('$titre')
                ->field('categorie')->first('$categorie')
                ->field('nombre')->sum(1)
            ->sort(['nombre' => 'desc', 'titre' => 'asc', '_id' => 'asc'])
            ->limit($limite);

        return $builder->getAggregation()->getIterator()->toArray();
    }
}