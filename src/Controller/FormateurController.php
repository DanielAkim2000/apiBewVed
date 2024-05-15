<?php

namespace App\Controller;

use App\Entity\Apprenant;
use App\Entity\Formateur;
use App\Repository\FormateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/formateur')]

class FormateurController extends AbstractController{
    #[Route('/', name: 'app_formateur_index', methods: ['GET'])]
    public function getAllFormateurs(FormateurRepository $formateurRepository){
        $formateurs = $formateurRepository->findAll();

        $formateurArray = [];
        foreach ($formateurs as $formateur) {
            $formateurArray[] = [
                'id' => $formateur->getId(),
                'prenom' => $formateur->getFirstname(),
                'nom' => $formateur->getLastname(),
            ];
        }

        return new JsonResponse($formateurArray, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/new', name: 'app_formateur_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager){
        $data = json_decode($request->getContent(), true);

        if (empty($data['prenom']) || empty($data['nom'])) {
            return new JsonResponse('Missing parameter', Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
        }

        $formateur = new Formateur();
        $formateur->setFirstname($data['prenom']);
        $formateur->setLastname($data['nom']);

        $entityManager->persist($formateur);
        $entityManager->flush();
        

        return new JsonResponse('Formateur created!', Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}', name: 'app_formateur_show', methods: ['GET'])]
    public function show(Formateur $formateur){
        $formateurArray = [
            'id' => $formateur->getId(),
            'prenom' => $formateur->getFirstname(),
            'nom' => $formateur->getLastname(),
        ];

        return new JsonResponse($formateurArray, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}/edit', name: 'app_formateur_update', methods: ['PUT'])]
    public function edit(Formateur $formateur, Request $request, EntityManagerInterface $entityManager){
        $data = json_decode($request->getContent(), true);

        if(empty($data['prenom']) || empty($data['nom'])){
            return new JsonResponse('Missing parameter', Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
        }

        $formateur->setFirstname($data['prenom']);
        $formateur->setLastname($data['nom']);

        $entityManager->persist($formateur);
        $entityManager->flush();

        return new JsonResponse('Formateur updated!', Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}', name: 'app_formateur_delete', methods: ['DELETE'])]
    public function delete(Formateur $formateur, EntityManagerInterface $entityManager){

        // si le formateur a des groupes en cours dans ses formations on les supprime aussi
        foreach ($formateur->getFormations() as $formation) {
            foreach ($formation->getGroupes() as $groupe) {
                $entityManager->remove($groupe);
            }
        }
        // si le formateur a des formations en cours on les supprime aussi
        foreach ($formateur->getFormations() as $formation) {
            $entityManager->remove($formation);
        }

        $entityManager->remove($formateur);
        $entityManager->flush();

        return new JsonResponse('Formateur deleted!', Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}