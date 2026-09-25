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
    /**
     * Lien de suivi : enregistre la consultation dans MongoDB puis redirige vers la ressource.
     * Un GET qui écrit, comme tout lien cliquable.
     * Pas de redirection ouverte : la destination vient de la base, pas de la requête.
     */
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
    // Valeur hors bornes : 400 plutôt que la 404 par défaut, pour ne pas laisser croire que la route n'existe pas.
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