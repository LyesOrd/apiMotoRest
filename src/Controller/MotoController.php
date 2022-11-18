<?php

namespace App\Controller;

use App\Entity\MotoSpec;
use App\Repository\ConcessionRepository;
use App\Repository\MotoSpecRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class MotoController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des motos.
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des motos",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=MotoSpec::class, groups={"getMotos"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="default")
     *
     * @param MotoSpecReposiitory $motoRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/motos', name: 'motos', methods: ['GET'])]
    public function getMotosSpec(MotoSpecRepository $motoRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getMotosSpec-" . $page . "-" . $limit;

        $jsonMotoList = $cache->get($idCache, function(ItemInterface $item) use ($motoRepository, $page, $limit, $serializer) {
            $item->tag("motoCache");
            $motoList = $motoRepository->findAllWithPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(["getMotos"]);

            return $serializer->serialize($motoList, 'json', $context);
        });
        
        return new JsonResponse($jsonMotoList, Response::HTTP_OK, [], true);
    }


    /**
     * Cette méthode permet de récupérer une moto en particulier en fonction de son id. 
     *
     * @param MotoSpec $moto
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/motos/{id}', name: 'detailsMotos', methods: ['GET'])]
    #[ParamConverter('motos', options: ['id' => 'idMoto'])]
    public function getDetailsMotosSpec(MotoSpec $moto, SerializerInterface $serializer): JsonResponse
    {   
        $context = SerializationContext::create()->setGroups(['getMotos']);
        $jsonMotoDetails = $serializer->serialize($moto, 'json', $context);
        return new JsonResponse($jsonMotoDetails, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de supprimer une moto par rapport à son id. 
     *
     * @param MotoSpec $moto
     * @param EntityManagerInterface $em
     * @return JsonResponse 
     */
    #[Route('api/motos/{id}', name: 'deleteMotos', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer une moto')]
    #[ParamConverter('moto', options: ['id' => 'idMoto'])]
    public function deleteMotos(MotoSpec $moto, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse
    {
        $moto->setStatus(false);
        $em->remove($moto);
        $em->flush();

        $cache->invalidateTags(["motoCache"]);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * Cette méthode permet d'insérer une nouvelle moto. 
     * Exemple de données : 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ConcessionRepository $concessionRepository
     * @return JsonResponse
     */
    #[Route('/api/motos', name:"createMotos", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer une moto')]
    public function createMotos(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGeneratorInterface, ConcessionRepository $concessionRepository, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse 
    {

        $motos = $serializer->deserialize($request->getContent(), MotoSpec::class, 'json');
        $motos->setStatus(true);
        $errors = $validator->validate($motos);

        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $idConcession = $content['idConcession'] ?? -1;
        $motos->setConcession($concessionRepository->find($idConcession));

        $em->persist($motos);
        $em->flush();

        $cache->invalidateTags(["motoCache"]);

        $context = SerializationContext::create()->setGroups(['getMotos']);
        $jsonMotos = $serializer->serialize($motos, 'json', $context);
        
        $location = $urlGeneratorInterface->generate('detailsMotos', ['idMoto' => $motos->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonMotos, Response::HTTP_CREATED, ["Location" => $location], true);
    }


    /**
     * Cette méthode permet de mettre à jour une moto en fonction de son id. 
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param MotoSpec $currentMoto
     * @param EntityManagerInterface $em
     * @param ConcessionRepository $concessionRepository
     * @return JsonResponse
     */
    #[Route('/api/motos/{id}', name:"updateMotos", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour éditer une moto')]
    public function updateMotos(Request $request, SerializerInterface $serializer, MotoSpec $currentMoto, EntityManagerInterface $em, ConcessionRepository $concessionRepository, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {
        $newMoto = $serializer->deserialize($request->getContent(), MotoSpec::class, 'json');

        $currentMoto->setType($newMoto->getType())
                    ->setRefroidissement($newMoto->getRefroidissement())
                    ->setCylindree($newMoto->getCylindree())
                    ->setPuissance($newMoto->getPuissance())
                    ->setPuissanceAuLitre($newMoto->getPuissanceAuLitre())
                    ->setReservoir($newMoto->getReservoir())
                    ->setPoids($newMoto->getPoids())
                    ->setTransmission($newMoto->getTransmission())
                    ->setCouleur($newMoto->getCouleur())
                    ->setPrix($newMoto->getPrix())
                    ->setStatus(true)
                    ->setConcession($newMoto->getConcession());

        $errors = $validator->validate($currentMoto);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $content = $request->toArray();
        $idMoto = $content['idMoto'] ?? -1;
        $currentMoto->setConcession($concessionRepository->find($idMoto));

        $em->persist($currentMoto);
        $em->flush();

        $cache->invalidateTags(["motoCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
