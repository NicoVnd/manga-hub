<?php

namespace App\Controller;

use App\Repository\AnimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnimeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(AnimeRepository $repo): Response
    {
        $top = $repo->findBy([], ['avgRating' => 'DESC'], 5);
        return $this->render('anime/home.html.twig', ['top' => $top]);
    }

    #[Route('/anime', name: 'anime_index')]
    public function index(AnimeRepository $repo): Response
    {
        $animes = $repo->findBy([], ['title' => 'ASC']);
        return $this->render('anime/index.html.twig', ['animes' => $animes]);
    }

    #[Route('/anime/{slug}', name: 'anime_show')]
    public function show(AnimeRepository $repo, string $slug): Response
    {
        $anime = $repo->findOneBy(['slug' => $slug]);
        if (!$anime) { throw $this->createNotFoundException(); }

        return $this->render('anime/show.html.twig', ['anime' => $anime]);
    }
}
