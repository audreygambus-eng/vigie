<?php

namespace App\Controller;

use App\Repository\RessourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Ressource;

#[Route('/api/ressources', format: 'json')]
class RessourceController extends AbstractController
{
    #[Route('', name: 'ressource_liste', methods: ['GET'])]
    public function liste(
        RessourceRepository $ressourceRepository,
        #[MapQueryParameter] ?int $categorie = null,
    ): JsonResponse {
        $criteres = $categorie === null ? [] : ['categorie' => $categorie];
        $ressources = $ressourceRepository->findBy($criteres, ['dateAjout' => 'DESC', 'id' => 'DESC']);

        return $this->json($ressources, context: ['groups' => 'ressource:lecture']);
    }

    #[Route('/{id}', name: 'ressource_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detail(Ressource $ressource): JsonResponse
    {
        return $this->json($ressource, context: ['groups' => 'ressource:lecture']);
    }
}