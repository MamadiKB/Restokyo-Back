<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Establishment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    #[Route('/api/user/{id}', name: 'getOnUser', methods: ['GET'])]
    public function getOnUser(User $user = null,  Security $security): JsonResponse
    {

        if ($security->getUser() !== $user) {
            throw $this->createAccessDeniedException('Non autorisé.');
        }
        
        if ($user === null) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($user, Response::HTTP_OK);
    }

    #[Route('/api/user/{id}/edit', name:"updateUser", methods:['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer,
                                User $currentUser = null, EntityManagerInterface $em, 
                                ValidatorInterface $validator, Security $security): JsonResponse 
    {
        $securityUser = $security->getUser();

        if ($securityUser !== $currentUser) {
            throw $this->createAccessDeniedException('Non autorisé.');
        }
        if ($currentUser === null) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }
        
        $content = $request->toArray();
        if ($content['password'] || $content['password'] === "") {
            throw $this->createAccessDeniedException('Non autorisé.');
        }
        
        $updatedUser = $serializer->deserialize($request->getContent(), 
                User::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);

        // On vérifie les erreurs
        $errors = $validator->validate($updatedUser);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }

        $em->persist($updatedUser);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

}
