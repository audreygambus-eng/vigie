<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RessourceControllerTest extends WebTestCase
{
    public function testCreationSansAuthentificationEstRefusee(): void
    {
        $client = static::createClient();
        // Données valides exprès : seule l'absence d'authentification peut expliquer le refus.
        $client->request(
            'POST',
            '/api/ressources',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'titre' => 'Ressource de test',
                'url' => 'https://exemple.fr',
                'categorieId' => 1,
            ]),
        );

        $this->assertResponseStatusCodeSame(401);
        $this->assertResponseHasHeader('WWW-Authenticate');
    }
}