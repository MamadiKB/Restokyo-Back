<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
}
