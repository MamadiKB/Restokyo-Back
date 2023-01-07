<?php

namespace App\Controller;


use App\Entity\Comment;
use App\Form\CommentType;
use App\Entity\Establishment;
use App\Repository\CommentRepository;
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

class CommentController extends AbstractController
{
    #[Route('/api/comment', name: 'getCommentList', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Non autorisé')]
    public function getCommentList(CommentRepository $commentRepository, SerializerInterface $serializer): JsonResponse
    {
        // Récupère tous les commentaires
        $commentList = $commentRepository->findAll();
        // Sérialise les commentaires en JSON
        $jsonCommentList = $serializer->serialize($commentList, 'json', ['groups' => 'getComment']);

        return new JsonResponse($jsonCommentList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/comment/{id}', name: 'getOnComent', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Non autorisé')]
    public function getOnComent(Comment $comment, SerializerInterface $serializer): JsonResponse
    {
        // Sérialise le commentaire en JSON
        $jsonComent = $serializer->serialize($comment, 'json', ['groups' => 'getComment']);
        return new JsonResponse($jsonComent, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/comment/establishment/{id}', name:"createComment", methods: ['POST'])]
    public function createComment(Establishment $establishment = null, Request $request,
                                EntityManagerInterface $em, Security $security,
                                SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        // Déserialise les données JSON de la requête en un objet Comment
        $newComment = $serializer->deserialize($request->getContent(), Comment::class, 'json');
        // Crée un formulaire de type CommentType à partir du nouveau commentaire
        $form = $this->createForm(CommentType::class, $newComment);
        // Décode les données JSON de la requête en un tableau associatif
        $data = json_decode($request->getContent(), true);
        // Si des erreurs sont détectées, elles sont renvoyées au client sous forme de JSON et une exception est levée
        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }

        // Envoie les données dans le formulaire et valide le formulaire
        $form->submit($data);
        // Form soumis et valide ?
        if ($form->isSubmitted() && $form->isValid()) {
            // On associe le commentaire a un etablisement
            $newComment->setEstablishment($establishment);
            // On associe le commentaire a l'utilistauer courant
            $newComment->setUser($security->getUser());
            
            // Enregistre le nouveau commentaire dans la base de données
            $em->persist($newComment);
            $em->flush();

            // Récupération de toutes les commentaires de l'établissement
            $establisComments = $establishment->getComments();
            // Calcul de la moyenne des notes
            $ratingSum = 0;
            foreach ($establisComments as $comment) {
                $ratingSum += $comment->getRating();
            }
            $ratingAverage = $ratingSum / count($establisComments);
            // Mise à jour de l'établissement avec la nouvelle moyenne des notes
            $establishment->setRating($ratingAverage);
            $em->persist($establishment);
            $em->flush();

            return $this->json(['message'=> 'Commentaire créé avec succès.'], Response::HTTP_CREATED);
        }
        
        return new JsonResponse(['message'=> 'erreur'], JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/api/comment/{id}/edit', name:"updateComment", methods:['PUT'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants')]
    public function updateComment(Request $request, Comment $currentComment = null, 
                                EntityManagerInterface $em, Security $security,
                                SerializerInterface $serializer,ValidatorInterface $validator ): JsonResponse 
    {
        // Récupère l'utilisateur connecté
        $securityUser = $security->getUser();
        // Si le commentaire n'existe pas, renvoie une erreur
        if ($currentComment === null) {
            return new JsonResponse(["error" => "Commentaire non selectioné"], Response::HTTP_NOT_FOUND);
        }
        // Si l'utilisateur connecté n'est pas l'auteur du commentaire, renvoie une erreur
        if ($securityUser !== $currentComment->getUser()) {
            return new JsonResponse(["error" => "ce n'est pas ton commentaire"], Response::HTTP_UNAUTHORIZED);
        }
        // Crée un formulaire de type CommentType à partir du commentaire existant
        $form = $this->createForm(CommentType::class, $currentComment);
        // Décode les données JSON de la requête en un tableau associatif
        $data = json_decode($request->getContent(), true);
        // Si des erreurs sont détectées, elles sont renvoyées au client sous forme de JSON et une exception est levée
        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }
        // Envoie la données dans le formulaire et valide le formulaire
        $form->submit($data);
        // Si le formulaire est soumis et valide, met à jour le commentaire existant
        if ($form->isSubmitted() && $form->isValid()) {
              // Déserialise les données JSON de la requête en un objet Comment
            $updatedComment = $serializer->deserialize($request->getContent(), 
                Comment::class, 
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentComment]);
                // Enregistre le commentaire modifié dans la base de données
                $em->persist($updatedComment);
                $em->flush();

                return $this->json(['message'=> 'Commentaire modifier avec succès.'], Response::HTTP_ACCEPTED);
        }

        return new JsonResponse(['message'=> 'erreur'], JsonResponse::HTTP_BAD_REQUEST);
    }


    #[Route('/api/comment/{id}', name: 'deleteComment', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN','ROLE_USER', message: 'Vous n\'avez pas les droits suffisants')]
    public function deleteComment(Comment $comment, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($comment);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
