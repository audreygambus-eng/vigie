<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\RessourceRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: RessourceRepository::class)]
#[UniqueEntity('url', message: 'Cette ressource est déjà enregistrée.')]
class Ressource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ressource:lecture'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.')]
    #[Groups(['ressource:lecture'])]
    private ?string $titre = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'L\'URL est obligatoire.')]
    #[Assert\Url(requireTld: true, message: 'Cette URL n\'est pas valide.')]
    #[Assert\Length(max: 255, maxMessage: 'L\'URL ne doit pas dépasser {{ limit }} caractères.')]
    #[Groups(['ressource:lecture'])]
    private ?string $url = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['ressource:lecture'])]
    private \DateTimeImmutable $dateAjout;

    // Relation unidirectionnelle : on ne navigue jamais d'une catégorie vers ses ressources
    // (le filtre par catégorie passe par le repository).
    // onDelete CASCADE : supprimer une catégorie supprime ses ressources, et c'est MySQL qui s'en charge.
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'La catégorie est obligatoire.')]
    #[Groups(['ressource:lecture'])]
    private ?Categorie $categorie = null;

        public function __construct()
    {
        $this->dateAjout = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getDateAjout(): \DateTimeImmutable
    {
        return $this->dateAjout;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }
}