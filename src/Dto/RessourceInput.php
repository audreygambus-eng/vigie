<?php

namespace App\Dto;

/**
 * Données reçues par l'API pour créer/modifier une ressource.
 * Le client envoie un identifiant de catégorie et l'entité attend
 * un objet Categorie : le contrôleur fait la traduction.
 * Seuls ces trois champs sont lus : tout autre champ envoyé (id, dateAjout…)
 * est ignoré, ce qui protège contre l'affectation de masse.
 */
use Symfony\Component\Validator\Constraints as Assert;

final class RessourceInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
        #[Assert\Length(max: 255, maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.')]
        public readonly string $titre = '',

        #[Assert\NotBlank(message:'L\'URL est obligatoire.')]
        #[Assert\Url(requireTld: true, message: 'Cette URL n\'est pas valide.')]
        #[Assert\Length(max: 255, maxMessage: 'L\'URL ne doit pas dépasser {{ limit }} caractères.')]
        public readonly string $url = '',

        #[Assert\NotNull(message: 'La catégorie est obligatoire.')]
        #[Assert\Positive(message: 'L\'identifiant de catégorie doit être un nombre positif.')]
        public readonly ?int $categorieId = null,
    ) {
    }
}