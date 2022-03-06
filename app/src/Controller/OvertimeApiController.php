<?php

namespace App\Controller;

use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OvertimeApiController extends AbstractController
{
    #[Route('/overtime/{hotel}/{dateRange}', name: 'app_overtime_api')]
    public function index(EntityManagerInterface $em, Hotel $hotel, int $dateRange): Response
    {
        return $this->json(
            $em->getRepository(Hotel::class)->getOverTime($hotel, $dateRange)
        );
    }
}
