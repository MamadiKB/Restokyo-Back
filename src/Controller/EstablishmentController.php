<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Establishment;
use App\Form\EstablishmentType;
use App\Repository\TagRepository;
use App\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EstablishmentRepository;
use Symfony\Bundle\SecurityBundle\Security;
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

    #[Route('/api/establishment/create', name:"createEstablishment", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un établissement')]
    public function createEstablishment(Request $request, SerializerInterface $serializer, 
                                        EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, 
                                        DistrictRepository $districtRepository, TagRepository $tagRepository,
                                        ValidatorInterface $validator): JsonResponse 
    {
        // Création d'un formulaire pour gérer les données de l'établissement
        $form = $this->createForm(EstablishmentType::class, new Establishment());
        // Décode les données JSON de la requête en un tableau associatif
        $data = json_decode($request->getContent(), true);
        // Si des erreurs sont détectées, elles sont renvoyées au client sous forme de JSON et une exception est levée
        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }
        // Envoie des données du formulaire et validation du formulaire
        $form->submit($data);
        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Désérialisation des données JSON de la requête en un objet Establishment
            $newEstablishment = $serializer->deserialize($request->getContent(), Establishment::class, 'json');
            // Récupération des données de la requête sous forme de tableau associatif
            $data = $request->toArray();
            // Récupération de l'ID du quartier associé à l'établissement
            $idDistrict = $data['idDistrict'] ?? -1;
            // Récupération de la liste des IDs de tags associés à l'établissement
            $idTags = $data['idTags'] ?? -1;
            // Association de l'établissement au quartier correspondant
            $newEstablishment->setDistrict($districtRepository->find($idDistrict));
            // Association de l'établissement aux tags correspondants
            foreach ($idTags as $value) {
                $newEstablishment->addTag($tagRepository->find($value));
            }
            // Enregistrement de l'établissement en base de données
            $em->persist($newEstablishment);
            $em->flush();
            // Génération de l'URL de l'établissement créé
            $location = $urlGenerator->generate('getOnEstablishment', ['id' => $newEstablishment->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $jsonEstablishment = $serializer->serialize($newEstablishment, 'json', ['groups' => 'getEstablishment']);
            return new JsonResponse($jsonEstablishment, Response::HTTP_CREATED, ["Location" => $location], true);
        }

        $errors = $form->getErrors(true);
        return new JsonResponse($errors, JsonResponse::HTTP_BAD_REQUEST, [], true);
    }

    #[Route('/api/establishment/{id}/edit', name:"updateEstablishment", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour update un établissement')]
    public function updateEstablishment(Request $request, SerializerInterface $serializer,
                                        Establishment $currentEstablishment, EntityManagerInterface $em, 
                                        DistrictRepository $districtRepository, ValidatorInterface $validator,
                                        TagRepository $tagRepository, UrlGeneratorInterface $urlGenerator): JsonResponse 
    {
        // Récupération de l'ID de l'établissement à partir de l'URL
        $id = $request->attributes->get('id');
        // Recherche de l'établissement dans la base de données
        $establishment = $em->getRepository(Establishment::class)->find($id);
        // Si l'établissement n'existe pas, renvoie une réponse avec le statut HTTP 404 Not Found
        if (!$establishment) {
            return new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
        }
        // Crée un formulaire de type EstablishmentType à partir de l'établissement courant
        $form = $this->createForm(EstablishmentType::class, $currentEstablishment);
        // Décode les données JSON de la requête en un tableau associatif
        $data = json_decode($request->getContent(), true);
        // Si des erreurs sont détectées, elles sont renvoyées au client sous forme de JSON et une exception est levée
        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }
        // Valide les données du formulaire
        $form->submit($data);
        // Si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Déserialise les données JSON de la requête en un objet Establishment
            $updatedEstablishment = $serializer->deserialize($request->getContent(), 
                Establishment::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEstablishment]);

            $data = $request->toArray();
            // Récupère l'ID du district s'il existe
            $idDistrict = $data['idDistrict'] ?? null;
            // Récupère la liste des IDs de tags s'ils existent
            $idTags = $data['idTags'] ?? null;
            // Si l'ID du district existe, on associe le district à l'établissement mis à jour
            if ($idDistrict !== null) {
                $updatedEstablishment->setDistrict($districtRepository->find($idDistrict));
            }
                // Si la liste des IDs de tags existe, on ajoute les tags à l'établissement mis à jour
            if ($idTags !== null) {
                // Récupérer la liste des tags actuels de l'établissement
                $currentTags = $currentEstablishment->getTags();
                // Parcours la liste des tags actuels
                foreach ($currentTags as $tag) {
                    // Supprime chaque tag de l'établissement en cours de modification
                    $currentEstablishment->removeTag($tag);
                }
                // Parcours la liste des tags envoyés dans la requête
                foreach ($idTags as $value) {
                    // Ajoute chaque tag à l'établissement en cours de modification
                    $updatedEstablishment->addTag($tagRepository->find($value));
                }
            }
            // Enregistre l'établissement modifier dans la base de données
            $em->persist($updatedEstablishment);
            $em->flush();
            // Génère l'URL de la nouvelle ressource créée
            $location = $urlGenerator->generate('getOnEstablishment', ['id' => $updatedEstablishment->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $jsonEstablishment = $serializer->serialize($updatedEstablishment, 'json', ['groups' => 'getEstablishment']);
            return new JsonResponse($jsonEstablishment, Response::HTTP_ACCEPTED, ["Location" => $location], true);
        }

        $formErrors = $form->getErrors(true);
        return new JsonResponse($formErrors, JsonResponse::HTTP_BAD_REQUEST, [], true);
    }

    #[Route('/api/establishment/{id}/delete', name: 'deleteEstablishment', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un établissement')]
    public function deleteEstablishment(Establishment $establishment, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($establishment);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }



    #[Route('/api/establishment/{id}/favori', name: 'favoriAdd', methods: ['PUT'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants')]
    public function favoriAdd(Request $request, SerializerInterface $serializer,
                                Establishment $currentEstablishment,
                                EntityManagerInterface $em, Security $security,
                                ValidatorInterface $validator): JsonResponse
    {

        // Déserialise les données JSON de la requête en un objet user
        $updatedUser = $serializer->deserialize($request->getContent(), 
            User::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $security->getUser()]);

        $updatedUser->addFavori($currentEstablishment);

        // Enregistre l'établissement modifier dans la base de données
        $em->persist($updatedUser);
        $em->flush();

        return $this->json(['message'=> 'ok'], Response::HTTP_OK);

    }
    
}
