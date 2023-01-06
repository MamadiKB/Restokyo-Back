<?php

namespace App\Controller;

use App\Entity\District;
use App\Entity\Establishment;
use App\Form\DistrictType;
use App\Repository\DistrictRepository;
use App\Repository\EstablishmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DistrictController extends AbstractController
{

    #[Route('/api/district', name: 'getDistrictList', methods: ['GET'])]
    public function getDistrictList(DistrictRepository $districtRepository, SerializerInterface $serializer): JsonResponse
    {
        $districtList = $districtRepository->findAll();
        $jsonDistrictList = $serializer->serialize($districtList, 'json', ['groups' => 'getDistrict']);
        return new JsonResponse($jsonDistrictList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/district/{id}', name: 'getOnDistrict', methods: ['GET'])]
    public function getOnDistrict(District $district, SerializerInterface $serializer): JsonResponse
    {
        $jsonDistrict = $serializer->serialize($district, 'json', ['groups' => 'getDistrict']);
        return new JsonResponse($jsonDistrict, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/district', name:"createDistrict", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un district')]
    public function createDistrict(Request $request, SerializerInterface $serializer, 
                                   EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator,
                                   ValidatorInterface $validator): JsonResponse 
    {
        $district = new District ;
        // Crée un formulaire de type CommentType à partir du nouveau commentaire
        $form = $this->createForm(DistrictType::class, $district);
        // Décode les données JSON de la requête en un tableau associatif
        $data = json_decode($request->getContent(), true);

        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }
        // Envoie les données dans le formulaire et valide le formulaire
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            // Déserialise les données JSON de la requête en un objet Comment
            $newDistrict = $serializer->deserialize($request->getContent(),
                District::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $district]);

            $em->persist($newDistrict);
            $em->flush();

            $jsonDistrict = $serializer->serialize($newDistrict, 'json', ['groups' => 'getDistrict']);
            $location = $urlGenerator->generate('getOnDistrict', ['id' => $newDistrict->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            return new JsonResponse($jsonDistrict, Response::HTTP_CREATED, ["Location" => $location], true);
        }

        return new JsonResponse(null ,Response::HTTP_BAD_REQUEST);
    }


    #[Route('/api/district/{id}', name:"updateDistrict", methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour update un district')]
    public function updateDistrict(Request $request, SerializerInterface $serializer,
                                   District $currentDistrict, EntityManagerInterface $em,
                                   UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse 
    {
        // Crée un formulaire de type CommentType à partir du nouveau commentaire
        $form = $this->createForm(DistrictType::class, $currentDistrict);
        // Décode les données JSON de la requête en un tableau associatif
        $data = json_decode($request->getContent(), true);

        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }
        // Envoie les données dans le formulaire et valide le formulaire
        $form->submit($data);

        $updatedDistrict = $serializer->deserialize($request->getContent(), 
                District::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentDistrict]);

            $em->persist($updatedDistrict);
            $em->flush();

        $jsonDistrict = $serializer->serialize($updatedDistrict, 'json', ['groups' => 'getDistrict']);
        $location = $urlGenerator->generate('getOnDistrict', ['id' => $updatedDistrict->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonDistrict, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/district/{id}', name: 'deleteDistrict', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un district')]
    public function deleteDistrict(District $district, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($district);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
