<?php

namespace App\Controller;

use App\Entity\Establishment;
use App\Repository\TagRepository;
use App\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EstablishmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EstablishmentController extends AbstractController
{
    #[Route('/api/establishment', name: 'getEstablishmentList', methods: ['GET'])]
    public function getEstablishmentList(EstablishmentRepository $establishmentRepository, SerializerInterface $serializer): JsonResponse
    {
        $establishmentList = $establishmentRepository->findAll();
        $jsonEstablishmentList = $serializer->serialize($establishmentList, 'json', ['groups' => 'getEstablishment']);
        return new JsonResponse($jsonEstablishmentList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/establishment/{id}', name: 'getOnEstablishment', methods: ['GET'])]
    public function getOnEstablishment(Establishment $establishment, SerializerInterface $serializer): JsonResponse
    {
        $jsonEstablishment = $serializer->serialize($establishment, 'json', ['groups' => 'getEstablishment']);
        return new JsonResponse($jsonEstablishment, Response::HTTP_OK, ['accept' => 'json'], true);
    }
   
    #[Route('/api/establishment/{id}', name: 'deleteEstablishment', methods: ['DELETE'])]
    public function deleteEstablishment(Establishment $establishment, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($establishment);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/establishment', name:"createEstablishment", methods: ['POST'])]
    public function createEstablishment(Request $request, SerializerInterface $serializer, 
                                        EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, 
                                        DistrictRepository $districtRepository, TagRepository $tagRepository): JsonResponse 
    {
        $establishment = $serializer->deserialize($request->getContent(), Establishment::class, 'json');
        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idDistrict. S'il n'est pas défini, alors on met -1 par défaut.
        $idDistrict = $content['idDistrict'] ?? -1;
        // On cherche le district qui correspond et on l'assigne a l'establishment.
        // Si "find" ne trouve pas le district, alors null sera retourné.
        $establishment->setDistrict($districtRepository->find($idDistrict));

        // Récupération de l'idTags. S'il n'est pas défini, alors on met -1 par défaut.
        $idTags = $content['idTags'] ?? -1;
        // On parcoure le tableu idTags qui contien l'id des tag.
        // Si "find" ne trouve pas le district, alors null sera retourné.
        foreach ($idTags as $value){
            $establishment->addTag($tagRepository->find($value));
        }

        $em->persist($establishment);
        $em->flush();

        $jsonEstablishment = $serializer->serialize($establishment, 'json', ['groups' => 'getEstablishment']);
        $location = $urlGenerator->generate('getOnEstablishment', ['id' => $establishment->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonEstablishment, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/establishment/{id}', name:"updateEstablishment", methods:['PUT'])]
    public function updateEstablishment(Request $request, SerializerInterface $serializer,
                                        Establishment $currentEstablishment, EntityManagerInterface $em, 
                                        DistrictRepository $districtRepository, TagRepository $tagRepository): JsonResponse 
    {
        $updatedEstablishment = $serializer->deserialize($request->getContent(), 
                Establishment::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEstablishment]);
        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();
        // Récupération de l'idDistrict. S'il n'est pas défini, alors on retourne le district de base.
        $idDistrict = $content['idDistrict'] ?? $currentEstablishment->getDistrict();
        // On cherche le district qui correspond et on l'assigne a l'establishment.
        // Si "find" ne trouve pas le district, alors null sera retourné.
        $updatedEstablishment->setDistrict($districtRepository->find($idDistrict));
        
        $updatedEstablishment->setUpdatedAt(new \DateTime('now'));
        
        $em->persist($updatedEstablishment);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }
    
}
