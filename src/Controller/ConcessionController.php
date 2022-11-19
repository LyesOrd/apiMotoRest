<?php

namespace App\Controller;

use App\Entity\Concession;
use App\Repository\ConcessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Annotations as OA;


class ConcessionController extends AbstractController
{
    /**
     * Récupère l'ensemble des concessionnaires.
     * 
     * @OA\Tag(name="ConcessionRoute")
     *
     * @param ConcessionRepository $concessionRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/concession', name: 'concession', methods: ['GET'])]
    public function getAllConcession(ConcessionRepository $concessionRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit',3);

        $idCache = "getAllConcession-" . $page . "-" . $limit;

        $jsonConcessionList = $cache->get($idCache, function (ItemInterface $item) use ($concessionRepository, $page, $limit, $serializer){
            $item->tag("motoCache");
            $concessionList = $concessionRepository->findAllWithPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(["getConcessions"]);

            return $serializer->serialize($concessionList, 'json', $context);
        });

        return new JsonResponse($jsonConcessionList, Response::HTTP_OK, [], true);
    }


    /**
     * Récupère une concession en particulier en fonction de son id.
     * 
     * @OA\Tag(name="ConcessionRoute")
     *
     * @param Concession $concession
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/concession/{id}', name: 'detailsConcessions', methods: ['GET'])]
    public function getDetailsConcession(Concession $concession, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getConcessions"]);
        $concessionDetails = $serializer->serialize($concession, 'json', $context);
        return new JsonResponse($concessionDetails, Response::HTTP_OK, [], true);
    }

     /**
     * Supprime une concession en fonction de son id.
     * 
     * En cascade, les motos associés aux concessions seront elles aussi supprimées. 
     * resynchronizer la base de données pour appliquer ces modifications. 
     * avec : php bin/console doctrine:schema:update --force
     * 
     * @OA\Tag(name="ConcessionRoute")
     * 
     * @param Concession $concession
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/concession/{id}', name: 'deleteConcessions', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer une concession')]
    public function deleteConcession(Concession $concession, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse {
        
        $em->remove($concession);
        $em->flush();

        $cache->invalidatetags(["motoCache"]);


        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Créer une nouvelle concession. 
     * Elle ne permet pas d'associer directement des motos à cette concession.
     * 
     * @OA\Tag(name="ConcessionRoute")
     * 
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *         example={
     *             "nom": "Honda",
     *              "pays": "JP",
     *              "slogan": "Be fast !",
     *              "status": true
     *            }
     *     )
     * )
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */

    #[Route('/api/concession', name: 'createConcessions', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer une nouvelle concession')]
    public function createConcession(Request $request, SerializerInterface $serializer,
        EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse {
        $concession = $serializer->deserialize($request->getContent(), Concession::class, 'json');

        $errors = $validator->validate($concession);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($concession);
        $em->flush();

        $cache->invalidateTags(["motoCache"]);

        $context = SerializationContext::create()->setGroups(["getConcessions"]);

        $jsonConcession = $serializer->serialize($concession, 'json', $context);
        $location = $urlGenerator->generate('detailsConcessions', ['id' => $concession->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonConcession, Response::HTTP_CREATED, ["Location" => $location], true);	
    }

    /**
     * Met à jour les données d'une concession.
     * 
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *         example={
     *             "nom": "Honda",
     *              "pays": "JP",
     *              "slogan": "Be fast !",
     *              "status": true
     *            }
     *     )
     * )
     * 
     * Cette méthode ne permet pas d'associer des motos et des concessions.
     * 
     * @OA\Tag(name="ConcessionRoute")
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Concession $currentConcession
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/concession/{id}', name:"updateConcessions", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour éditer une concession')]
    public function updateConcession(Request $request, SerializerInterface $serializer,
        Concession $currentConcession, EntityManagerInterface $em, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse {
        
        $errors = $validator->validate($currentConcession);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $newConcession = $serializer->deserialize($request->getContent(), Concession::class, 'json');
        $currentConcession->setNom($newConcession->getNom())
                            ->setPays($newConcession->getPays())
                            ->setSlogan($newConcession->getSlogan());
        $em->persist($currentConcession);
        $em->flush();

        $cache->invalidateTags(["motoCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);

    }
}
