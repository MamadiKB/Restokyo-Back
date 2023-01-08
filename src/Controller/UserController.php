<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserController extends AbstractController
{

    #[Route('/api/user/{id}', name: 'getOnUser', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants')]
    public function getOnUser(User $user = null,  Security $security): JsonResponse
    {
        // Vérifie si l'utilisateur actuel est l'utilisateur ciblé ou s'il a le rôle 'ROLE_ADMIN'
        if ($security->getUser() !== $user && !$security->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Non autorisé.');
        }
        // Si l'utilisateur n'existe pas, renvoie une réponse avec le statut HTTP 404 Not Found
        if ($user === null) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }
        // Renvoie l'utilisateur avec le statut HTTP 200 OK
        return $this->json($user, Response::HTTP_OK);
    }

    #[Route('/api/register', name:"createUser", methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em,  
                            UserPasswordHasherInterface $passwordHasher, SerializerInterface $serializer,
                            ValidatorInterface $validator): JsonResponse
    {
        $user = new User;
        // Crée un formulaire de type CommentType à partir du nouveau commentaire
        $form = $this->createForm(UserType::class, $user);
        // Décode les données JSON de la requête en un tableau associatif
        $data = json_decode($request->getContent(), true);
        
        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        // Envoie les données dans le formulaire et valide le formulaire
        $form->submit($data);
        // Form soumis et valide ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Déserialise les données JSON de la requête en un objet User
            $newUser = $serializer->deserialize($request->getContent(),
                User::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);

            $newUser->setRoles(['ROLE_USER']);
            // recupéré le mot de passe traité par le formulaire
            $passwordInForm = $form->get('password')->getData();
            // On doit hacher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($newUser, $passwordInForm);
            // On l'écrase dans le $user
            $newUser->setPassword($hashedPassword);
            // Enregistre le nouveau utilisateur dans la base de données
            $em->persist($newUser);
            $em->flush();

            return $this->json(['message'=> 'Utilisateur créé avec succès.'], Response::HTTP_CREATED);
        }

        return new JsonResponse($form->getErrors(true), JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/api/user/{id}/edit', name:"updateUser", methods:['PUT'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants')]
    public function updateUser(Request $request, User $currentUser = null, 
                                EntityManagerInterface $em, Security $security, 
                                UserPasswordHasherInterface $passwordHasher,
                                SerializerInterface $serializer, ValidatorInterface $validator ): JsonResponse 
    {
        $securityUser = $security->getUser();

        if ($currentUser === null) {
            return new JsonResponse(['error' => 'Non autorisé'], Response::HTTP_NOT_FOUND);
        }
        if ($securityUser !== $currentUser) {
            return new JsonResponse(['error' => 'Non autorisé'], Response::HTTP_NOT_FOUND);
        }
        // création d'une instance du formulaire 'Usertype' et lui passer l'utilisateur a modifiée
        $form = $this->createForm(UserType::class, $currentUser);
        // décodé le contenu de la requête
        $data = json_decode($request->getContent(), true);

        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        // envoiyé les données dans le form et validé le formulaire
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
        // Déserialise les données JSON de la requête en un objet User
        $updatedUser = $serializer->deserialize($request->getContent(), 
            User::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);
            // Si mot de passe présent dans le formulaire,
            // on hâche
            $passwordInForm = $form->get('password')->getData();
            if ($passwordInForm) {
                // On doit hacher le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($updatedUser, $passwordInForm);
                // On l'écrase dans le $user
                $updatedUser->setPassword($hashedPassword);
            }
                $em->persist($updatedUser);
                $em->flush();

            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }

        return new JsonResponse($form->getErrors(true), JsonResponse::HTTP_BAD_REQUEST);
    }
    
}
