<?php

namespace App\DataFixtures;

use App\Entity\Emplacement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class EmplacementFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $adresseFixe = '02 Rue Turquie 1001 Tunis';
        $batiments = ['A', 'B'];
        $etages = range(1, 10); 

        foreach ($batiments as $batiment) {
            foreach ($etages as $etage) {
                $emplacement = new Emplacement();
                $emplacement->setAdresse($adresseFixe);
                $emplacement->setBatiment($batiment);
                $emplacement->setNumEtage($etage);
                
                $manager->persist($emplacement);
            }
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['emplacement_group']; // Nom du groupe
    }
}
