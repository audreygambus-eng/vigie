<?php

namespace App\Controller;

use App\Repository\RessourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Ressource;
use App\Dto\RessourceInput;
use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/ressources', format: 'json')]
class RessourceController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'ressource_liste', methods: ['GET'])]
    public function liste(
        RessourceRepository $ressourceRepository,
        #[MapQueryParameter] ?int $categorie = null,
    ): JsonResponse {
        $criteres = $categorie === null ? [] : ['categorie' => $categorie];
        $ressources = $ressourceRepository->findBy($criteres, ['dateAjout' => 'DESC', 'id' => 'DESC']);

        return $this->json($ressources, context: ['groups' => 'ressource:lecture']);
    }

    // \d+ : l'identifiant doit être numérique, pour ne pas capter d'autres adresses
    // comme /api/ressources/top.
    #[Route('/{id}', name: 'ressource_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detail(Ressource $ressource): JsonResponse
    {
        return $this->json($ressource, context: ['groups' => 'ressource:lecture']);
    }
    #[Route('', name: 'ressource_creation', methods: ['POST'])]
    public function creation(
        #[MapRequestPayload] RessourceInput $input,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $categorie = $this->trouverCategorie($input->categorieId, $entityManager);

        $ressource = (new Ressource())
            ->setTitre($input->titre)
            ->setUrl($input->url)
            ->setCategorie($categorie);

        $this->valider($ressource);
        $entityManager->persist($ressource);
        $entityManager->flush();

        return $this->json(
            $ressource,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('ressource_detail', ['id' => $ressource->getId()])],
            ['groups' => 'ressource:lecture'],
        );
    }

    #[Route('/{id}', name: 'ressource_modification', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function modification(
        Ressource $ressource,
        #[MapRequestPayload] RessourceInput $input,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $ressource
            ->setTitre($input->titre)
            ->setUrl($input->url)
            ->setCategorie($this->trouverCategorie($input->categorieId, $entityManager));
        
        $this->valider($ressource);
        $entityManager->flush();

        return $this->json($ressource, context: ['groups' => 'ressource:lecture']);
    }

    #[Route('/{id}', name: 'ressource_suppression', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function suppression(Ressource $ressource, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($ressource);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function trouverCategorie(int $id, EntityManagerInterface $entityManager): Categorie
    {
        $categorie = $entityManager->find(Categorie::class, $id);
        if ($categorie === null) {
            // 422 et non 404 : l'adresse appelée existe, ce sont les données envoyées qui sont invalides.
            throw new UnprocessableEntityHttpException('Catégorie inconnue.');
        }

        return $categorie;
    }

    /**
     * Valide l'entité en plus du DTO : UniqueEntity doit interroger la base,
     * ce que la validation du DTO ne peut pas faire.
     * L'index unique est la garantie finale si deux requêtes arrivent en même temps.
     */
    private function valider(Ressource $ressource): void
    {
        $violations = $this->validator->validate($ressource);
        if (count($violations) > 0) {
            throw new UnprocessableEntityHttpException(
                'Données invalides.',
                new ValidationFailedException($ressource, $violations),
            );
        }
    }
    
}