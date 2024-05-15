<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Entity\Promotion;
use App\Form\CompetenceType;
use App\Repository\CompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/competence')]
class CompetenceController extends AbstractController {
    #[Route('/', name: 'app_competence_index', methods: ['GET'])]
    public function getAllCompetences ( CompetenceRepository $competenceRepository){
        $competences = $competenceRepository->findAll();

        $competenceArray = [];
        foreach ($competences as $competence) {
            $competenceArray[] = [
                'id' => $competence->getId(),
                'nom' => $competence->getName(),
            ];
        }

        return new JsonResponse($competenceArray, 200, ['Content-Type' => 'application/json']);
    }
}