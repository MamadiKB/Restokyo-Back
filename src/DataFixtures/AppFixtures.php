<?php

namespace App\DataFixtures;

use App\Entity\District;
use App\Entity\Establishment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des district.
        $listDistrict = [];
        for ($i = 0; $i < 12; $i++) {
            // Création du district lui-même.
            $district = new District();
            $district->setName("the" . $i);
            $district->setKanji("漢字" . $i);
            $district->setSlug("/the" . $i);
            $manager->persist($district);
            // On sauvegarde le district créé dans un tableau.
            $listDistrict[] = $district;
        }

        // Création d'une vingtaine d'establishment ayant pour titre
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
            // On lie le district à un establishment pris au hasard dans le tableau des auteurs.
            $establishment->setDistrict($listDistrict[array_rand($listDistrict)]);
            $manager->persist($establishment);
        }

        $manager->flush();
    }
}
