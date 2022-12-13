<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TagController extends AbstractController
{
    #[Route('/api/tag', name: 'getTagList', methods: ['GET'])]
    public function getTagList(TagRepository $tagRepository, SerializerInterface $serializer): JsonResponse
    {
        $tagList = $tagRepository->findAll();
        $jsonTagList = $serializer->serialize($tagList, 'json', ['groups' => 'getTag']);
        return new JsonResponse($jsonTagList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/tag/{id}', name: 'getOnTag', methods: ['GET'])]
    public function getOnTag(Tag $tag, SerializerInterface $serializer): JsonResponse
    {
        $jsonTag = $serializer->serialize($tag, 'json', ['groups' => 'getTag']);
        return new JsonResponse($jsonTag, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/tag', name:"createTag", methods: ['POST'])]
    public function createTag(Request $request, SerializerInterface $serializer, 
                              EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator,
                              EstablishmentRepository $establishmentRepository, ValidatorInterface $validator ): JsonResponse 
    {
        $tag = $serializer->deserialize($request->getContent(), Tag::class, 'json');
        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();
        // Récupération de l'idEstablishment. S'il n'est pas défini, alors on met -1 par défaut.
        $idEstablishment = $content['idEstablishment'] ?? -1;
        // On parcoure le tableu idEstablishment qui contien l'id des Establishment.
        // Si "find" ne trouve pas l'Establishment, alors null sera retourné.
        foreach ($idEstablishment as $value){
            $tag->addEstablishment($establishmentRepository->find($value));
        }

        $errors = $validator->validate($tag);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }

        $em->persist($tag);
        $em->flush();

        $jsonTag = $serializer->serialize($tag, 'json', ['groups' => 'getTag']);
        $location = $urlGenerator->generate('getOnTag', ['id' => $tag->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonTag, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/tag/{id}', name:"updateTag", methods:['PUT'])]
    public function updateTag(Request $request, SerializerInterface $serializer,
                              Tag $currentTag, EntityManagerInterface $em,
                              EstablishmentRepository $establishmentRepository, ValidatorInterface $validator ): JsonResponse 
    {
        $updatedTag = $serializer->deserialize($request->getContent(), 
                Tag::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentTag]);

        $errors = $validator->validate($updatedTag);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }

        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();
        // Récupération de l'idEstablishment. S'il n'est pas défini, alors on met -1 par défaut.
        $idEstablishment = $content['idEstablishment'] ?? -1;
        // On parcoure le tableu idEstablishment qui contien l'id des Establishment.
        // Si "find" ne trouve pas l'Establishment, alors null sera retourné.
        foreach ($idEstablishment as $value){
            $updatedTag->addEstablishment($establishmentRepository->find($value));
        }

        $em->persist($updatedTag);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
    
    #[Route('/api/tag/{id}', name: 'deleteTag', methods: ['DELETE'])]
    public function deleteTag(Tag $tag, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($tag);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
