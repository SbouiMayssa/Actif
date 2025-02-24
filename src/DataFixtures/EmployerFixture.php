<?php

namespace App\DataFixtures;
use App\Entity\Employer;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
class EmployerFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $employer=new Employer();
        $employer->setNom('Sboui');
        $employer->setPrenom('Amal');
        $manager->persist($employer);

        $employer1=new Employer();
        $employer1->setNom('Mejri');
        $employer1->setPrenom('Sirine');
        $manager->persist($employer1);

        
    

        $faker= Factory::create('fr_FR');
        for($i=0;$i<10;$i++){
            $personne=new Employer();
            $personne->setNom($faker->firstname);
            $personne->setPrenom($faker->name);
        
        $manager->persist($personne);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['employer'] ;
       }
}
