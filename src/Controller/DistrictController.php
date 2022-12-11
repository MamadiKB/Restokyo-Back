<?php

namespace App\Controller;

use App\Entity\District;
use App\Repository\DistrictRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
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

}
