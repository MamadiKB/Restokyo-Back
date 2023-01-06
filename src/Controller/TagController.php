<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
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

    #[Route('/api/tag/create', name:"createTag", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un tag')]
    public function createTag(Request $request, SerializerInterface $serializer, 
                            EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator,
                            ValidatorInterface $validator): JsonResponse
    {   
        $tag = new tag ;
        // Crée un formulaire de type CommentType à partir du nouveau commentaire
        $form = $this->createForm(TagType::class, $tag);
        // Décode les données JSON de la requête en un tableau associatif
        $data = json_decode($request->getContent(), true);

        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }
        // Envoie les données dans le formulaire et valide le formulaire
        $form->submit($data);
        // Form soumis et valide ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Déserialise les données JSON de la requête en un objet Comment
            $newTag = $serializer->deserialize($request->getContent(),
            Tag::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $tag]);

            $em->persist($newTag);
            $em->flush();

            $jsonDistrict = $serializer->serialize($newTag, 'json', ['groups' => 'getTag']);
            $location = $urlGenerator->generate('getOnTag', ['id' => $newTag->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            return new JsonResponse($jsonDistrict, Response::HTTP_CREATED, ["Location" => $location], true);
            // return $this->json(['message'=> 'Tag créé avec succès.'], Response::HTTP_CREATED);
        }

        return new JsonResponse($form->getErrors(true), JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/api/tag/{id}/update', name:"updateTag", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour uptate un tag')]
    public function updateTag(Request $request, SerializerInterface $serializer,
                              Tag $currentTag, EntityManagerInterface $em,UrlGeneratorInterface $urlGenerator,
                              ValidatorInterface $validator): JsonResponse 
    {
        // Crée un formulaire de type CommentType à partir du nouveau commentaire
        $form = $this->createForm(TagType::class, $currentTag);
        // Décode les données JSON de la requête en un tableau associatif
        $data = json_decode($request->getContent(), true);

        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }
        // Envoie les données dans le formulaire et valide le formulaire
        $form->submit($data);
        
        // Form soumis et valide ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Déserialise les données JSON de la requête en un objet
            $updatedTag = $serializer->deserialize($request->getContent(), 
            Tag::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentTag]);
            
            $em->persist($updatedTag);
            $em->flush();

            $jsonDistrict = $serializer->serialize($updatedTag, 'json', ['groups' => 'getTag']);
            $location = $urlGenerator->generate('getOnTag', ['id' => $updatedTag->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            return new JsonResponse($jsonDistrict, Response::HTTP_CREATED, ["Location" => $location], true);
            // return $this->json(['message'=> 'Tag créé avec succès.'], Response::HTTP_CREATED);
        }

        return new JsonResponse($form->getErrors(true), JsonResponse::HTTP_BAD_REQUEST);
    }
    
    #[Route('/api/tag/{id}/delete', name: 'deleteTag', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un tag')]
    public function deleteTag(Tag $tag, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($tag);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
