<?php

namespace App\Controller;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories', format: 'json')]
class CategorieController extends AbstractController
{
    #[Route('', name: 'categorie_liste', methods: ['GET'])]
    public function liste(EntityManagerInterface $entityManager): JsonResponse
    {
        $categories = $entityManager->getRepository(Categorie::class)->findBy([], ['libelle' => 'ASC']);

        return $this->json($categories, context: ['groups' => 'categorie:lecture']);
    }
}