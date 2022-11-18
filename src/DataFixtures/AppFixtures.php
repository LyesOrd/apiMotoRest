<?php

namespace App\DataFixtures;

use App\Entity\Concession;
use App\Entity\MotoSpec;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private Generator $faker;

    /**
     * Classe Hashant le mdp
     * @var UserPassswordHasherInterface
     */
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher){

        $this->faker = Factory::create('fr_FR');
        $this->userPasswordHasher = $userPasswordHasher;
    }



    public function load(ObjectManager $manager): void
    {

        $user = new User();
        $user->setEmail("user@motoapi.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);

        // Création d'un user admin

        $userAdmin = new User();
        $userAdmin->setEmail("admin@motoapi.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);

        $listConcession = [];
        for ($i=0; $i < 50 ; $i++) { 
            $concession = new Concession();
            $concession->setNom($this->faker->word());
            $concession->setPays($this->faker->countryCode());
            $concession->setSlogan($this->faker->sentence());
            $concession->setStatus(true);
            $listConcession[] = $concession;
            $manager->persist($concession);
        }
        

        for ($i=0; $i < 50 ; $i++) {
            
            $moto = new MotoSpec();
            $moto->setType($this->faker->sentence());
            $moto->setRefroidissement($this->faker->word());
            $moto->setCylindree($this->faker->randomNumber());
            $moto->setPuissance($this->faker->numberBetween(50,1000));
            $moto->setPuissanceAuLitre($this->faker->numberBetween(50,1000));
            $moto->setReservoir($this->faker->randomNumber(2, true));
            $moto->setPoids($this->faker->randomNumber(3, true));
            $moto->setTransmission($this->faker->word());
            $moto->setCouleur($this->faker->colorName());
            $moto->setPrix($this->faker->randomNumber(5, false));
            $moto->setStatus(true);
            $moto->setConcession($listConcession[array_rand($listConcession)]);
            
            $manager->persist($moto);
        }

        $manager->flush();
    }
}
