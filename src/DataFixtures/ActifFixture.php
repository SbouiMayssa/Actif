<?php

namespace App\DataFixtures;

use App\Entity\Actif;
use App\Entity\Emplacement;
use App\Entity\Employer;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class ActifFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Récupérer les entités existantes
        $emplacements = $manager->getRepository(Emplacement::class)->findAll();
        $employers = $manager->getRepository(Employer::class)->findAll();

        // Récupérer TOUS les utilisateurs et filtrer ceux ayant ROLE_ADMIN en PHP
        $allUsers = $manager->getRepository(User::class)->findAll();
        $users = array_filter($allUsers, function (User $user) {
            return in_array('ROLE_ADMIN', $user->getRoles());
        });
        $users = array_values($users); // Réindexer le tableau

        // Vérification des données
        if (empty($users)) {
            throw new \Exception('Aucun utilisateur admin trouvé. Assurez-vous d\'avoir des utilisateurs avec ROLE_ADMIN dans la base.');
        }

        if (empty($employers)) {
            throw new \Exception('Aucun employeur trouvé. Assurez-vous d\'avoir des employeurs dans la base.');
        }

        // Listes de marques et types d'actifs
        $marques = ['HP', 'Dell', 'Apple', 'Lenovo', 'Acer', 'Asus', 'Samsung', 'Epson'];
        $types = ['Ordinateur', 'Imprimante', 'Souris', 'Clavier', 'Moniteur'];

        // Créer des actifs
        for ($i = 0; $i < 15; $i++) {
            $actif = new Actif();
            $type = $faker->randomElement($types); // Générer le type ici pour réutilisation
        
            $actif->setNom($faker->randomElement($marques))
                  ->setType($type)
                  ->setDateAcquisation($faker->dateTimeThisDecade)
                  ->setLocation($faker->randomElement($emplacements))
                  ->setNumSerie($faker->regexify('[A-Z0-9]{10}')) // Numéro de série avec lettres et chiffres
                  ->setCreatedBy($faker->randomElement($users)) 
                  ->setEtat($faker->randomElement(['fonctionnel', 'en panne', 'remplacé']));
        
            if ($type === 'Ordinateur') {
                // Assigner un seul employé pour un ordinateur
                $actif->addUserAssigned($faker->randomElement($employers));
            } else {
                // Assigner plusieurs employés pour les autres types
                $assignedEmployers = $faker->randomElements($employers, rand(1, 3)); // 1 à 3 employés
                foreach ($assignedEmployers as $employer) {
                    $actif->addUserAssigned($employer);
                }
            }
        
            $manager->persist($actif);
        }
        
        // Sauvegarde
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['actif_group'];
    }
}
