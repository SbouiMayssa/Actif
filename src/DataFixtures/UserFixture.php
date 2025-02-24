<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;
class UserFixture extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private UserPasswordHasherInterface $hasher){}
    public function load(ObjectManager $manager): void
    {
        $admin1=new User();
        $admin1->setNom('Mayssa');
        $admin1->setPrenom('Sboui');
        $admin1->setEmail('MayssaSboui@gmail.com');
        $admin1->setPassword($this->hasher->hashPassword($admin1,'mayssa123'));
        $admin1->setRoles(['ROLE_ADMIN']);


        $admin2=new User();
        $admin2->setNom('Soumaya');
        $admin2->setPrenom('Rahali');
        $admin2->setEmail('soumayarahli@gmail.com');
        $admin2->setPassword($this->hasher->hashPassword($admin2,'soumaya123'));
        $admin2->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin1);
        $manager->persist($admin2);


        $tech5=new User();
        $tech5->setNom('Ahmed');
        $tech5->setPrenom('Jbali');
        $tech5->setEmail('ahmedjbali@gmail.com');
        $tech5->setPassword($this->hasher->hashPassword($tech5,'ahmed123'));
        $tech5->setRoles(['ROLE_TECH']);

        $manager->persist($tech5);

       
        
            
            
        $faker = Factory::create('fr_FR');
        for ($i=0; $i <=3; $i++) { 
            $tech=new User();
            $tech->setNom($faker->lastName);
            $tech->setPrenom($faker->firstName);
            $tech->setEmail("technicien$i@gmail.com");
            $tech->setPassword($this->hasher->hashPassword($tech,'tech123'));
            $tech->setRoles(['ROLE_TECH']);
            $manager->persist($tech);
        }



        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['user'] ;
       }
}
