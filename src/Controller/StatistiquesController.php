<?php

namespace App\Controller;

use App\Entity\Ressource;
use App\Service\StatistiquesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class StatistiquesController extends AbstractController
{
    #[Route('/ressources/{id}/consulter', name: 'ressource_consulter', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function consulter(Ressource $ressource, StatistiquesService $statistiques): RedirectResponse
    {
        $statistiques->enregistrerConsultation(
            $ressource->getId(),
            $ressource->getTitre(),
            $ressource->getCategorie()->getLibelle(),
        );

        return $this->redirect($ressource->getUrl());
    }

    #[Route('/api/statistiques/top', name: 'statistiques_top', methods: ['GET'], format: 'json')]
    public function top(
        StatistiquesService $statistiques,
        #[MapQueryParameter(options: ['min_range' => 1, 'max_range' => 365], validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        int $jours = 30,
        #[MapQueryParameter(options: ['min_range' => 1, 'max_range' => 50], validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        int $limite = 10,
    ): JsonResponse {
        $depuis = $statistiques->debutPeriode($jours);

        return $this->json([
            'jours' => $jours,
            'depuis' => $depuis->format(\DateTimeInterface::ATOM),
            'top' => $statistiques->topRessources($depuis, $limite),
        ]);
    }
}