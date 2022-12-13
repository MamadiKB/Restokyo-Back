<?php

namespace App\Controller;

use App\Entity\District;
use App\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    #[Route('/api/district/{id}', name:"updateDistrict", methods: ['PUT'])]
    public function updateDistrict(Request $request, SerializerInterface $serializer,
                                   District $currentDistrict, EntityManagerInterface $em,
                                   ValidatorInterface $validator ): JsonResponse 
    {
        $updatedDistrict = $serializer->deserialize($request->getContent(), 
                District::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentDistrict]);
        // On vérifie les erreurs
        $errors = $validator->validate($updatedDistrict);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }

        $em->persist($updatedDistrict);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/district', name:"createDistrict", methods: ['POST'])]
    public function createDistrict(Request $request, SerializerInterface $serializer, 
                                   EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator,
                                   ValidatorInterface $validator ): JsonResponse 
    {
        $district = $serializer->deserialize($request->getContent(), District::class, 'json');
        // On vérifie les erreurs
        $errors = $validator->validate($district);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }

        $em->persist($district);
        $em->flush();

        $jsonDistrict = $serializer->serialize($district, 'json', ['groups' => 'getDistrict']);
        $location = $urlGenerator->generate('getOnDistrict', ['id' => $district->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonDistrict, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/district/{id}', name: 'deleteDistrict', methods: ['DELETE'])]
    public function deleteDistrict(District $district, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($district);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
