<?php

namespace App\Controller;

use App\Entity\Apprenant;
use App\Entity\Promotion;
use App\Form\ApprenantType;
use App\Repository\ApprenantRepository;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/promotion')]
class PromotionController extends AbstractController{

    #[Route('/', name: 'app_promotion_index', methods: ['GET'])]
    public function getAllPromotions(PromotionRepository $promotionRepository, SerializerInterface $serializer): Response
    {
        $promotions = $promotionRepository->findAll();

        $promotionArray = [];
        foreach ($promotions as $promotion) {
            $promotionArray[] = [
                'id' => $promotion->getId(),
                'nom' => $promotion->getName(),
                'apprenants' => $promotion->getApprenants()
            ];
        }

        return new JsonResponse($promotionArray, 200, ['Content-Type' => 'application/json']);

    }

    #[Route('/new', name: 'app_promotion_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nom'])) {
            return new JsonResponse('Missing parameter', Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
        }

        $promotion = new Promotion();
        $promotion->setName($data['nom']);

        $entityManager->persist($promotion);
        $entityManager->flush();

        return new JsonResponse('Promotion created!', Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
    }
    
    #[Route('/{id}', name: 'app_promotion_show', methods: ['GET'])]
    public function show(Promotion $promotion): Response
    {
        $promotionArray = [
            'id' => $promotion->getId(),
            'nom' => $promotion->getName(),
            'apprenants' => $promotion->getApprenants()
        ];

        return new JsonResponse($promotionArray, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}/edit', name: 'app_promotion_update', methods: ['PUT'])]
    public function edit(Promotion $promotion, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nom'])) {
            return new JsonResponse('Missing parameter', Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
        }

        $promotion->setName($data['nom']);

        $entityManager->persist($promotion);
        $entityManager->flush();

        return new JsonResponse('Promotion updated!', Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    #[Route('/{id}', name: 'app_promotion_delete', methods: ['DELETE'])]
    public function delete(Promotion $promotion, EntityManagerInterface $entityManager, PromotionRepository $promotionRepository): Response
    {
        $promotionDefault = $promotionRepository->find(1);
        // si il y a des apprennants qui appartiennent a cette promo on leur met le promo par defaut
        $apprenants = $promotion->getApprenants();
        foreach ($apprenants as $apprenant) {
            $apprenant->setPromotion($promotionDefault);
            $entityManager->persist($apprenant);
            $entityManager->flush();
        }

        $entityManager->remove($promotion);
        $entityManager->flush();


        return new JsonResponse('Promotion deleted!', Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

}