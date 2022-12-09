<?php

namespace App\DataFixtures;

use App\Entity\Establishment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création d'une vingtaine de restaurants ayant pour titre
        $typeArray = ['Restaurant', 'Izakaya'];
        for ($i = 0; $i < 20; $i++) {
            $key = array_rand($typeArray);
            $establishment = new Establishment;
            $establishment->setName('establishment ' . $i);
            $establishment->setType($typeArray[$key]);
            $establishment->setDescription('lorem ' . $i);
            $establishment->setAddress('rue ' . $i);
            $establishment->setPrice(30);
            $establishment->setWebsite('https: ' . $i);
            $establishment->setPhone(689613315);
            $establishment->setRating(2.3);
            $establishment->setSlug('slug ' . $i);
            $establishment->setPicture('https: ' . $i);
            $establishment->setStatus(1);
            $establishment->setOpeningTime('du ' . $i . ' a ' .$i);
            $manager->persist($establishment);
        }

        $manager->flush();
    }
}
