<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class RessourceInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public readonly string $titre = '',

        #[Assert\NotBlank]
        #[Assert\Url(requireTld: true)]
        #[Assert\Length(max: 255)]
        public readonly string $url = '',

        #[Assert\NotNull]
        #[Assert\Positive]
        public readonly ?int $categorieId = null,
    ) {
    }
}