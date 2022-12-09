<?php

namespace App\Controller;

use App\Repository\EstablishmentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EstablishmentController extends AbstractController
{
    #[Route('/api/establishment', name: 'getEstablishmentList', methods: ['GET'])]
    public function getEstablishmentList(EstablishmentRepository $establishmentRepository, SerializerInterface $serializer): JsonResponse
    {
        $establishmentList = $establishmentRepository->findAll();
        $jsonEstablishmentList = $serializer->serialize($establishmentList, 'json');
        return new JsonResponse($jsonEstablishmentList, Response::HTTP_OK, [], true);
    }
}
