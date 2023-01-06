<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Comment;
use App\Entity\District;
use App\Entity\Establishment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un user "normal"
        for ($i = 0; $i < 2; $i++) {
            $user = new User();
            $user->setEmail("user$i@bookapi.com");
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
            $user->setPseudo("USER$i");
            $manager->persist($user);
        }

        // Création d'un user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@bookapi.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $userAdmin->setPseudo("ADMIN");
        $manager->persist($userAdmin);

        // Création des tags
        $tagList = [];
        for ($i = 0; $i < 14; $i++) {
            $tag = new Tag();
            $tag->setName("tag $i");
            $tag->setSlug("/slug$i");
            $manager->persist($tag);
            // On sauvegarde le tag créé dans un tableau.
            $tagList[] = $tag; 
        }
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


        // Création des district.
        $listComment = [];
        //$dateImmutable = new \DateTimeImmutable('2022-12-21 10:00:00');
        for ($i = 0; $i < 20; $i++) {
            // Création du district lui-même.
            $comment = new Comment();
            $comment->setContent("c'est un bon retaurant $i ! ");
            $comment->setUser($user);
            //$comment->setPublishedAt($dateImmutable);
            $manager->persist($comment);

            $listComment[] = $comment;
        }

        // creation d"un tableux contenant les type
        $typeArray = ['Restaurant', 'Izakaya'];
        // Création d'une vingtaine d'establishment ayant pour titre
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
            // on ajoute deux tags pris au hasard
            $establishment->addTag($tagList[array_rand($tagList)]);
            $establishment->addTag($tagList[array_rand($tagList)]);
            $establishment->addComment($listComment[array_rand($listComment)]);
            
            
            $manager->persist($establishment);
        }

        $manager->flush();
    }
}
