<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Categorie;
use App\Entity\Ressource;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
       $libelles = [
            'Symfony & PHP',
            'Bases de données',
            'Conteneurs & DevOps',
            'Tests & qualité',
            'Sécurité',
            'Front-end',
        ];

        $categories = [];
        foreach ($libelles as $libelle) {
            $categorie = (new Categorie())->setLibelle($libelle);
            $manager->persist($categorie);
            $categories[$libelle] = $categorie;
        }

        $ressources = [
            ['Documentation Symfony', 'https://symfony.com/doc/current/index.html', 'Symfony & PHP'],
            ['Manuel PHP', 'https://www.php.net/manual/fr/', 'Symfony & PHP'],
            ['Doctrine ORM', 'https://www.doctrine-project.org/projects/orm.html', 'Bases de données'],
            ["Pipeline d'agrégation MongoDB", 'https://www.mongodb.com/docs/manual/aggregation/', 'Bases de données'],
            ['Docker Compose', 'https://docs.docker.com/compose/', 'Conteneurs & DevOps'],
            ['Documentation PHPUnit', 'https://docs.phpunit.de/', 'Tests & qualité'],
            ['OWASP Top 10', 'https://owasp.org/www-project-top-ten/', 'Sécurité'],
            ['Sécurité dans Symfony', 'https://symfony.com/doc/current/security.html', 'Sécurité'],
            ['MDN Web Docs', 'https://developer.mozilla.org/fr/', 'Front-end'],
            ['Référentiel RGAA', 'https://accessibilite.numerique.gouv.fr/', 'Front-end'],
        ];

        foreach ($ressources as [$titre, $url, $libelleCategorie]) {
            $ressource = (new Ressource())
                ->setTitre($titre)
                ->setUrl($url)
                ->setCategorie($categories[$libelleCategorie]);
            $manager->persist($ressource);
        }


        $manager->flush();
    }
}
