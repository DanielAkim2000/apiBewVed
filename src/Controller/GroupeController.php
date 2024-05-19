<?php

namespace App\Controller;

use App\Entity\Apprenant;
use App\Entity\Formation;
use App\Entity\Groupe;
use App\Form\GroupeType;
use App\Repository\FormateurRepository;
use App\Repository\GroupeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/groupe')]
class GroupeController extends AbstractController
{
    #[Route('/', name: 'app_groupe_index', methods: ['GET'])]
    public function index(GroupeRepository $groupeRepository, FormateurRepository $formateurRepository): Response
    {
        $groupes = $groupeRepository->findAll();

        $groupeArray = [];
        foreach ($groupes as $groupe) {
            $groupeArray[] = [
                'id' => $groupe->getId(),
                'nom' => $groupe->getName(),
                'formateur' => $groupe->getFormateur()->getFirstname() . ' ' . $groupe->getFormateur()->getLastname(),
                'apprenants' => $groupe->getApprenants()->map(function ($apprenant) {
                    return [
                        'id' => $apprenant->getId(),
                        'nom' => $apprenant->getFirstname(),
                        'prenom' => $apprenant->getLastname(),
                        'sexe' => $apprenant->getSexe(),
                        'age' => $apprenant->getAge()
                    ];
                })->toArray(),
                'formation' => $groupe->getFormation()->getName()
            ];
        }

        return new JsonResponse($groupeArray, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/new', name: 'app_groupe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $groupe = new Groupe();
        $data = json_decode($request->getContent(), true);

        $groupe->setName($data['name']);
        $groupe->setFormation($entityManager->getRepository(Formation::class)->find($data['formation']));


        //Insertion des apprenants
        foreach ($data['apprenants'] as $apprenant) {
            $groupe->addApprenant($entityManager->getRepository(Apprenant::class)->find($apprenant));
        }

        $entityManager->persist($groupe);
        $entityManager->flush();

        $groupeArray = [];
        $groupeArray[] = [
            'id' => $groupe->getId(),
            'name' => $groupe->getName(),
            'formation' => $groupe->getFormation()->getName(),
            'apprenants' => $groupe->getApprenants()->map(function ($apprenant) {
                return [
                    'id' => $apprenant->getId(),
                    'nom' => $apprenant->getFirstname(),
                    'prenom' => $apprenant->getLastname()
                ];
            })->toArray(),
        ];

        return new JsonResponse($groupeArray, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}', name: 'app_groupe_show', methods: ['GET'])]
    public function show(Groupe $groupe): Response
    {
        return $this->render('groupe/show.html.twig', [
            'groupe' => $groupe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_groupe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Groupe $groupe, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        $groupe->setName($data['name']);
        $groupe->setFormation($entityManager->getRepository(Formation::class)->find($data['formation']));

        // Suppression des apprenants
        $groupe->getApprenants()->clear();
        //Insertion des apprenants
        foreach ($data['apprenants'] as $apprenant) {
            $groupe->addApprenant($entityManager->getRepository(Apprenant::class)->find($apprenant));
        }

        $entityManager->persist($groupe);
        $entityManager->flush();

        $groupeArray = [];
        $groupeArray[] = [
            'id' => $groupe->getId(),
            'name' => $groupe->getName(),
            'formation' => $groupe->getFormation()->getName(),
            'apprenants' => $groupe->getApprenants()->map(function ($apprenant) {
                return [
                    'id' => $apprenant->getId(),
                    'nom' => $apprenant->getFirstname(),
                    'prenom' => $apprenant->getLastname()
                ];
            })->toArray(),
        ];

        return new JsonResponse($groupeArray, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}', name: 'app_groupe_delete', methods: ['POST'])]
    public function delete(Request $request, Groupe $groupe, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($groupe);
        $entityManager->flush();

        return $this->redirectToRoute('app_groupe_index', [], Response::HTTP_SEE_OTHER);
    }
}
