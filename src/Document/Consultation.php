<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;

/**
 * La consultation est écrite une fois et jamais modifiée.
 * Le titre et la catégorie sont dupliqués depuis MySQL : les statistiques
 * restent lisibles même si la ressource est renommée ou supprimée.
 */

#[ODM\Document(collection: 'consultations')]
#[ODM\Index(keys: ['dateConsultation' => 'desc'])]
class Consultation
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'int')]
    private int $ressourceId;

    #[ODM\Field(type: 'string')]
    private string $titre;

    #[ODM\Field(type: 'string')]
    private string $categorie;

    #[ODM\Field(type: 'date_immutable')]
    private \DateTimeImmutable $dateConsultation;

        public function __construct(
        int $ressourceId,
        string $titre,
        string $categorie,
        ?\DateTimeImmutable $dateConsultation = null,
    ) {
        $this->ressourceId = $ressourceId;
        $this->titre = $titre;
        $this->categorie = $categorie;
        $this->dateConsultation = $dateConsultation ?? new \DateTimeImmutable();
    }

        public function getId(): ?string
    {
        return $this->id;
    }

    public function getRessourceId(): int
    {
        return $this->ressourceId;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getCategorie(): string
    {
        return $this->categorie;
    }

    public function getDateConsultation(): \DateTimeImmutable
    {
        return $this->dateConsultation;
    }
}