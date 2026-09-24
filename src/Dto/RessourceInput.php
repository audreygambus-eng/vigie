<?php

namespace App\Dto;

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