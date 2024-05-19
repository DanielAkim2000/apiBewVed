<?php

namespace App\Controller;

use App\Entity\Apprenant;
use App\Entity\Promotion;
use App\Repository\ApprenantRepository;
use App\Repository\CompetenceRepository;
use App\Repository\FormationRepository;
use App\Repository\GroupeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/apprenant')]
class ApprenantController extends AbstractController
{
    #[Route('/', name: 'app_apprenant_index', methods: ['GET'])]
    public function getAllApprenant(ApprenantRepository $apprenantRepository, FormationRepository $formationRepository, SerializerInterface $serializer): Response
    {
        // Récupération des apprenants et des formations
        $apprenants = $apprenantRepository->findAll();
        // Récupération des formations
        $formations = $formationRepository->findAll();

        $apprenantArray = [];
        foreach ($apprenants as $apprenant) {
            // Récupération des formations de l'apprenant
            $formationsofApprenant = [];
            foreach ($formations as $formation) {
                if ($formation->getApprenants()->contains($apprenant)) {
                    $formationsofApprenant[] = [
                        'id' => $formation->getId(),
                        'nom' => $formation->getName(),
                    ];
                }
            }

            // Recuperation des competences de l'apprenant 
            $competences = [];
            foreach ($apprenant->getCompetences() as $competence) {
                $competences[] = [
                    'id' => $competence->getId(),
                    'nom' => $competence->getName(),
                ];
            }
            // Insertion des données dans le tableau
            $apprenantArray[] = [
                'id' => $apprenant->getId(),
                'prenom' => $apprenant->getFirstname(),
                'nom' => $apprenant->getLastname(),
                'age' => $apprenant->getAge(),
                'sexe' => $apprenant->getSexe(),
                'promotion' => $apprenant->getPromotion()->getName(),
                'competences' => $competences,
                'formations' => $formationsofApprenant

            ];
        }

        return new JsonResponse($apprenantArray, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}', name: 'app_apprenant_show', methods: ['GET'])]
    public function show(Apprenant $apprenant): Response
    {
        // Recuperation des competences de l'apprenant 
        $competences = [];
        foreach ($apprenant->getCompetences() as $competence) {
            $competences[] = [
                'id' => $competence->getId(),
                'nom' => $competence->getName(),
            ];
        }
        $apprenantArray = [];
        $apprenantArray[] = [
            'id' => $apprenant->getId(),
            'prenom' => $apprenant->getFirstname(),
            'nom' => $apprenant->getLastname(),
            'age' => $apprenant->getAge(),
            'sexe' => $apprenant->getSexe(),
            'competences' => $competences,
            'promotion' => $apprenant->getPromotion()->getId(),
        ];

        return new JsonResponse($apprenantArray, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/new', name: 'app_apprenant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CompetenceRepository $competenceRepository): Response
    {
        // Vérification des données
        if (!$request->getContent()) {
            return new JsonResponse(['error' => 'Veuillez renseigner le contenu de la requête'], 400, ['Content-Type' => 'application/json']);
        }
        if (!$request->headers->get('Content-Type') === 'application/json') {
            return new JsonResponse(['error' => 'Veuillez renseigner le bon Content-Type'], 400, ['Content-Type' => 'application/json']);
        }

        // Récupération des données
        $data = json_decode($request->getContent(), true);


        if (!isset($data['firstname'])) {
            return new JsonResponse(['error' => 'Veuillez renseigner le prénom'], 400, ['Content-Type' => 'application/json']);
        }
        if (!isset($data['lastname'])) {
            return new JsonResponse(['error' => 'Veuillez renseigner le nom'], 400, ['Content-Type' => 'application/json']);
        }
        if (!isset($data['age'])) {
            return new JsonResponse(['error' => 'Veuillez renseigner l\'âge'], 400, ['Content-Type' => 'application/json']);
        }
        if (!isset($data['sexe']) || $data['sexe'] === null || $data['sexe'] === "") {
            return new JsonResponse(['error' => 'Veuillez renseigner le sexe'], 400, ['Content-Type' => 'application/json']);
        }
        if (!isset($data['promotion']) || $data['promotion'] === null || $data['promotion'] === "") {
            return new JsonResponse(['error' => 'Veuillez renseigner la promotion'], 400, ['Content-Type' => 'application/json']);
        }
        if (!isset($data['competences'])) {
            return new JsonResponse(['error' => 'Veuillez renseigner les compétences'], 400, ['Content-Type' => 'application/json']);
        }

        // Création d'un objet Apprenant
        $apprenant = new Apprenant();
        // Insertion des données dans l'objet Apprenant
        $apprenant->setFirstname($data['firstname']);
        $apprenant->setLastname($data['lastname']);
        $apprenant->setAge($data['age']);
        $apprenant->setSexe($data['sexe']);
        // Récupération de la promotion
        $promotion = $entityManager->getRepository(Promotion::class)->find($data['promotion']);
        // Insertion de la promotion dans l'apprenant
        $apprenant->setPromotion($promotion);

        // Récupération des compétences et insertion dans l'apprenant
        foreach ($data['competences'] as $competence) {
            $apprenant->addCompetence($competenceRepository->find($competence));
        }

        // Insertion de l'apprenant dans la base de données
        $entityManager->persist($apprenant);
        $entityManager->flush();

        // Insertion des données dans le tableau
        $apprenantArray = [];
        $apprenantArray[] = [
            'id' => $apprenant->getId(),
            'prenom' => $apprenant->getFirstname(),
            'nom' => $apprenant->getLastname(),
            'age' => $apprenant->getAge(),
            'sexe' => $apprenant->getSexe(),
            'promotion' => $apprenant->getPromotion()->getName()
        ];

        return new JsonResponse($apprenantArray, 201, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}/edit', name: 'app_apprenant_edit', methods: ['PUT'])]
    public function edit(Request $request, Apprenant $apprenant, EntityManagerInterface $entityManager, CompetenceRepository $competenceRepository): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['firstname'])) {
            return new JsonResponse(['error' => 'Veuillez renseigner le prénom'], 400, ['Content-Type' => 'application/json']);
        }
        if (!isset($data['lastname'])) {
            return new JsonResponse(['error' => 'Veuillez renseigner le nom'], 400, ['Content-Type' => 'application/json']);
        }
        if (!isset($data['age'])) {
            return new JsonResponse(['error' => 'Veuillez renseigner l\'âge'], 400, ['Content-Type' => 'application/json']);
        }
        if (!isset($data['sexe'])) {
            return new JsonResponse(['error' => 'Veuillez renseigner le sexe'], 400, ['Content-Type' => 'application/json']);
        }
        if (!isset($data['promotion'])) {
            return new JsonResponse(['error' => 'Veuillez renseigner la promotion'], 400, ['Content-Type' => 'application/json']);
        }
        if (!isset($data['competences'])) {
            return new JsonResponse(['error' => 'Veuillez renseigner les compétences'], 400, ['Content-Type' => 'application/json']);
        }

        // Insertion des données dans l'objet Apprenant
        $apprenant->setFirstname($data['firstname']);
        $apprenant->setLastname($data['lastname']);
        $apprenant->setAge($data['age']);
        $apprenant->setSexe($data['sexe']);
        // Récupération de la promotion
        $promotion = $entityManager->getRepository(Promotion::class)->find($data['promotion']);
        // Insertion de la promotion dans l'apprenant
        $apprenant->setPromotion($promotion);

        // Suppression des compétences
        $apprenant->getCompetences()->clear();

        // Récupération des compétences et insertion dans l'apprenant
        foreach ($data['competences'] as $competence) {
            $apprenant->addCompetence($competenceRepository->find($competence['id']));
        }


        $entityManager->persist($apprenant);
        $entityManager->flush();

        $apprenantArray = [];
        $apprenantArray[] = [
            'id' => $apprenant->getId(),
            'nom' => $apprenant->getFirstname(),
            'prenom' => $apprenant->getLastname(),
            'age' => $apprenant->getAge(),
            'sexe' => $apprenant->getSexe(),
            'promotion' => $apprenant->getPromotion()->getName()
        ];

        return new JsonResponse($apprenantArray, 201, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}', name: 'app_apprenant_delete', methods: ['DELETE'])]
    public function delete(Request $request, Apprenant $apprenant, GroupeRepository $groupeRepository, EntityManagerInterface $entityManager): Response
    {
        // recuperation du groupe de l'utilisateur 
        $groupes = $groupeRepository->findAll();
        $groupesApprenant = [];
        foreach ($groupes as $groupe) {
            if ($groupe->getApprenants()->contains($apprenant)) {
                $groupesApprenant[] = $groupe;
            }
        }
        $entityManager->remove($apprenant);
        $entityManager->flush();

        // suppression des groupes vides
        foreach ($groupesApprenant as $groupe) {
            if ($groupe->getApprenants()->isEmpty()) {
                $entityManager->remove($groupe);
            }
        }
        $entityManager->flush();


        return new JsonResponse([], 204, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}/ajoutFormation', name: 'app_apprenant_ajoutFormation', methods: ['POST'])]
    public function ajoutFormation(Request $request, Apprenant $apprenant, FormationRepository $formationRepository, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        // recherche de la formation
        $formation = $formationRepository->find($data['formation']);

        // ajout de l'apprenant dans la formation
        $formation->addApprenant($apprenant);

        $entityManager->persist($formation);
        $entityManager->flush();

        return new JsonResponse([], 201, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}/removeFormation', name: 'app_apprenant_removeFormation', methods: ['POST'])]
    public function removeFormation(Request $request, Apprenant $apprenant, FormationRepository $formationRepository, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['formation'])) {
            return new JsonResponse(['error' => 'Veuillez renseigner la formation'], 400, ['Content-Type' => 'application/json']);
        }

        $formation = $formationRepository->find($data['formation']);

        $formation->removeApprenant($apprenant);
        // suppression des apprenants dans les groupes de la formation dans lesquels il y esy
        foreach ($formation->getGroupes() as $groupe) {
            if ($groupe->getApprenants()->contains($apprenant)) {
                $groupe->removeApprenant($apprenant);
                $entityManager->persist($groupe);
            }
            // si le groupe est vide on le supprime
            if ($groupe->getApprenants()->isEmpty()) {
                $entityManager->remove($groupe);
            }
        }

        $entityManager->persist($formation);
        $entityManager->flush();

        return new JsonResponse([], 201, ['Content-Type' => 'application/json']);
    }
}
