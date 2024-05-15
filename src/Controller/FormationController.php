<?php

namespace App\Controller;

use App\Entity\Apprenant;
use App\Entity\Formateur;
use App\Entity\Formation;
use App\Entity\Groupe;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/formation')]
class FormationController extends AbstractController
{
    #[Route('/', name: 'app_formation_index', methods: ['GET'])]
    public function getAllFormations(FormationRepository $formationRepository): Response
    {
        $formations = $formationRepository->findAll();

        $formationArray = [];
        foreach ($formations as $formation) {
            $formationArray[] = [
                'id' => $formation->getId(),
                'nom' => $formation->getName(),
                'duree' => $formation->getDuree(),
                'formateur' => $formation->getFormateur()->getFirstname() . ' ' . $formation->getFormateur()->getLastname(),
                'groupes' => $formation->getGroupes()->map(function ($groupe) {
                    return [
                        'id' => $groupe->getId(),
                        'nom' => $groupe->getName(),
                        'apprenants' => $groupe->getApprenants()->map(function ($apprenant) {
                            return [
                                'id' => $apprenant->getId(),
                                'nom' => $apprenant->getFirstname(),
                                'prenom' => $apprenant->getLastname()
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
                'apprenants' => $formation->getApprenants()->map(function ($apprenant) {
                    return [
                        'id' => $apprenant->getId(),
                        'nom' => $apprenant->getFirstname(),
                        'prenom' => $apprenant->getLastname()
                    ];
                })->toArray(),
            ];
        }

        return new JsonResponse($formationArray, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/new', name: 'app_formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        $formation = new Formation();
        // Insertion des données dans l'objet Apprenant
        $formation->setName($data['name']);
        $formation->setDuree($data['duree']);
        $formation->setFormateur($entityManager->getRepository(Formateur::class)->find($data['formateur']));


        $entityManager->persist($formation);
        $entityManager->flush();

        $formationArray = [];
        $formationArray[] = [
            'id' => $formation->getId(),
            'name' => $formation->getName(),
            'duree' => $formation->getDuree(),
            'formateur' => $formation->getFormateur()->getFirstname() . ' ' . $formation->getFormateur()->getLastname(),
            'groupes' => $formation->getGroupes()->map(function ($groupe) {
                return [
                    'id' => $groupe->getId(),
                    'nom' => $groupe->getName()
                ];
            })->toArray(),
            'apprenants' => $formation->getApprenants()->map(function ($apprenant) {
                return [
                    'id' => $apprenant->getId(),
                    'prenom' => $apprenant->getFirstname(),
                    'nom' => $apprenant->getLastname()
                ];
            })->toArray(),
        ];

        return new JsonResponse($formationArray, 201, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}', name: 'app_formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        // formateur 
        $formateurArray = [];
        $formateur  = $formation->getFormateur();
        $formateurArray[] = [
            'id' => $formateur->getId(),
            'prenom' => $formateur->getFirstname(),
            'nom' => $formateur->getLastname(),
            'formations' => $formateur->getFormations()->map(function ($formation) {
                return [
                    'id' => $formation->getId(),
                    'nom' => $formation->getName(),
                    'duree' => $formation->getDuree(),
                    'groupes' => $formation->getGroupes()->map(function ($groupe) {
                        return [
                            'id' => $groupe->getId(),
                            'nom' => $groupe->getName()
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ];


        $formationArray = [];
        $formationArray[] = [
            'id' => $formation->getId(),
            'nom' => $formation->getName(),
            'duree' => $formation->getDuree(),
            'formateur' => $formateurArray,
            'groupes' => $formation->getGroupes()->map(function ($groupe) {
                return [
                    'id' => $groupe->getId(),
                    'nom' => $groupe->getName(),
                    'apprenants' => $groupe->getApprenants()->map(function ($apprenant) {
                        return [
                            'id' => $apprenant->getId(),
                            'nom' => $apprenant->getFirstname(),
                            'prenom' => $apprenant->getLastname(),
                            'sexe' => $apprenant->getSexe(),
                            'age' => $apprenant->getAge(),
                            'competences' => $apprenant->getCompetences()->map(function ($competence) {
                                return [
                                    'id' => $competence->getId(),
                                    'nom' => $competence->getName()
                                ];
                            })->toArray(),
                        ];
                    })->toArray(),
                ];
            })->toArray(),
            'apprenants' => $formation->getApprenants()->map(function ($apprenant) {
                return [
                    'id' => $apprenant->getId(),
                    'nom' => $apprenant->getFirstname(),
                    'prenom' => $apprenant->getLastname()
                ];
            })->toArray(),
        ];

        return new JsonResponse($formationArray, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}/edit', name: 'app_formation_edit', methods: ['PUT'])]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        // Insertion des données dans l'objet Apprenant
        $formation->setName($data['name']);
        $formation->setDuree($data['duree']);
        $formation->setFormateur($entityManager->getRepository(Formateur::class)->find($data['formateur']['id']));


        $entityManager->persist($formation);
        $entityManager->flush();



        $formationArray = [];
        $formationArray[] = [
            'id' => $formation->getId(),
            'name' => $formation->getName(),
            'duree' => $formation->getDuree(),
            'formateur' => $formation->getFormateur()->getFirstname() . ' ' . $formation->getFormateur()->getLastname(),
            'groupes' => $formation->getGroupes()->map(function ($groupe) {
                return [
                    'id' => $groupe->getId(),
                    'nom' => $groupe->getName()
                ];
            })->toArray(),
        ];

        return new JsonResponse($formationArray, 200, ['Content-Type' => 'application/json']);
    }



    #[Route('/{id}', name: 'app_formation_delete', methods: ['DELETE'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        // si il y a des groupes il faudra les supprimer avant 
        foreach ($formation->getGroupes() as $groupe) {
            $entityManager->remove($groupe);
        }

        $entityManager->remove($formation);
        $entityManager->flush();

        return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/addgroup', name: 'app_formation_addgroup', methods: ['POST'])]
    public function addGroup(Request $request, Formation $formation, EntityManagerInterface $entityManager)
    {
        // Suppression des anciens groupes
        foreach ($formation->getGroupes() as $groupe) {
            $entityManager->remove($groupe);
        }

        $data = json_decode($request->getContent(), true);
        $listeapprenants = $formation->getApprenants();
        $apprenants = $listeapprenants->toArray();
        // Calcul du nombre d'apprenants
        $nbApprenants = count($apprenants);
        // Calcul du nombre de groupes
        $nbGroupes = ceil($nbApprenants / $data['nbApprenantsParGroupe']);

        // Filtre des apprenants par critère
        if ($data['critere'] == ['age']) {
            // Tri des apprenants par age

            usort($apprenants, function ($a, $b) {
                return $a->getAge() <=> $b->getAge();
            });
        } else if ($data['critere'] == ['sexe']) {
            // Tri des apprenants par sexe

            usort($apprenants, function ($a, $b) {
                return $a->getSexe() <=> $b->getSexe();
            });
        } else if ($data['critere'] == ['competence']) {
            // Tri des apprenants par compétence

            usort($apprenants, function ($a, $b) {
                return count($a->getCompetences()) <=> count($b->getCompetences());
            });
        } else if ($data['critere'] == ['age', 'competence']) {
            // Tri des apprenants par age et compétence

            usort($apprenants, function ($a, $b) {
                if ($a->getAge() == $b->getAge()) {
                    return count($a->getCompetences()) <=> count($b->getCompetences());
                }
                return $a->getAge() <=> $b->getAge();
            });
        } else if ($data['critere'] == ['age', 'sexe']) {
            // Tri des apprenants par age et sexe

            usort($apprenants, function ($a, $b) {
                if ($a->getAge() == $b->getAge()) {
                    return $a->getSexe() <=> $b->getSexe();
                }
                return $a->getAge() <=> $b->getAge();
            });
        } else if ($data['critere'] == ['sexe', 'competence']) {
            // Tri des apprenants par sexe et compétence

            usort($apprenants, function ($a, $b) {
                if ($a->getSexe() == $b->getSexe()) {
                    return count($a->getCompetences()) <=> count($b->getCompetences());
                }
                return $a->getSexe() <=> $b->getSexe();
            });
        } else if ($data['critere'] == ['sexe', 'age', 'competence']) {
            // Tri des apprenants par sexe, age et compétence

            usort($apprenants, function ($a, $b) {
                if ($a->getSexe() == $b->getSexe()) {
                    if ($a->getAge() == $b->getAge()) {
                        return count($a->getCompetences()) <=> count($b->getCompetences());
                    }
                    return $a->getAge() <=> $b->getAge();
                }
                return $a->getSexe() <=> $b->getSexe();
            });
        }

        // creation des groupes
        for ($i = 0; $i < $nbGroupes; $i++) {
            // Création du groupe
            $groupe = new Groupe();
            // Insertion des données dans l'objet Groupe
            $groupe->setName('Groupe ' . ($i + 1) . ' ' . $formation->getName());
            $groupe->setFormation($formation);



            // Ajout des apprenants dans le groupe
            for ($j = 0; $j < $data['nbApprenantsParGroupe']; $j++) {
                if (isset($apprenants[$j])) {
                    // Ajout de l'apprenant dans le groupe
                    $groupe->addApprenant($apprenants[$j]);

                    // Suppression de l'apprenant de la liste des apprenants
                    unset($apprenants[$j]);
                }
            }

            // Réindexer le tableau $apprenants après suppression d'un élément
            $apprenants = array_values($apprenants);


            $entityManager->persist($groupe);
            $entityManager->flush();
        }

        return new JsonResponse(['message' => 'Groupes créés avec succès'], 201, ['Content-Type' => 'application/json']);
    }
}
