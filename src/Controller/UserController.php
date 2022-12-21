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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


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
    public function updateUser(Request $request, User $currentUser = null, 
                                EntityManagerInterface $em, Security $security, 
                                UserPasswordHasherInterface $passwordHasher ): JsonResponse 
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
        // envoiyé les données dans le form et validé le formulaire
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si mot de passe présent dans le formulaire,
            // on hâche
            $passwordInForm = $form->get('password')->getData();
            if ($passwordInForm) {
                // On doit hacher le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($currentUser, $passwordInForm);
                // On l'écrase dans le $user
                $currentUser->setPassword($hashedPassword);
            }
                $em->persist($currentUser);
                $em->flush();

            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['error' => $form->isValid()], 400);
    }
}
