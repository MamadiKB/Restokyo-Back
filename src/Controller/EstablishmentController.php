<?php

namespace App\Controller;

use App\Entity\Establishment;
use App\Form\EstablishmentType;
use App\Repository\TagRepository;
use App\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EstablishmentRepository;
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
        $form = $this->createForm(EstablishmentType::class, new Establishment());
        $data = json_decode($request->getContent(), true);

        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }

        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $newEstablishment = $serializer->deserialize($request->getContent(), Establishment::class, 'json');

            $data = $request->toArray();
            $idDistrict = $data['idDistrict'] ?? -1;
            $idTags = $data['idTags'] ?? -1;

            $newEstablishment->setDistrict($districtRepository->find($idDistrict));
            foreach ($idTags as $value) {
                $newEstablishment->addTag($tagRepository->find($value));
            }

            $em->persist($newEstablishment);
            $em->flush();
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

        $form = $this->createForm(EstablishmentType::class, $currentEstablishment);
        $data = json_decode($request->getContent(), true);
        
        $errors = $validator->validate($data);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }
    
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $updatedEstablishment = $serializer->deserialize($request->getContent(), 
                Establishment::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEstablishment]);

            $data = $request->toArray();
            $idDistrict = $data['idDistrict'] ?? null;
            $idTags = $data['idTags'] ?? null;

            if ($idDistrict !== null) {
                $updatedEstablishment->setDistrict($districtRepository->find($idDistrict));
            }
            if ($idTags !== null) {
                // Récupérer la liste des tags actuels de l'établissement
                $currentTags = $currentEstablishment->getTags();
                foreach ($currentTags as $tag) {
                    $currentEstablishment->removeTag($tag);
                }
                foreach ($idTags as $value) {
                    $updatedEstablishment->addTag($tagRepository->find($value));
                }
            }

            $em->persist($updatedEstablishment);
            $em->flush();
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
    
}
