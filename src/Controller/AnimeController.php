<?php

namespace App\Controller;


use App\Entity\UserAnime;
use App\Enum\WatchingStatus;
use App\Form\UserAnimeType;
use App\Repository\AnimeRepository;
use App\Repository\UserAnimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function show(
        string $slug,
        AnimeRepository $repo,
        UserAnimeRepository $uaRepo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $anime = $repo->findOneBy(['slug' => $slug]);
        if (!$anime) {
            throw $this->createNotFoundException();
        }

        // Récupère la ligne user<->anime si elle existe
        $track = null;
        if ($this->getUser()) {
            $track = $uaRepo->findOneBy(['user' => $this->getUser(), 'anime' => $anime]);
            if (!$track) {
                $track = (new UserAnime())
                    ->setUser($this->getUser())
                    ->setAnime($anime);
                // Pas de statut par défaut: il reste null tant que l'utilisateur ne choisit pas
            }

            $form = $this->createForm(UserAnimeType::class, $track);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $track->setUpdatedAt(new \DateTime());
                $em->persist($track);
                $em->flush();

                $this->addFlash('success', 'Tes préférences ont été enregistrées.');
                return $this->redirectToRoute('anime_show', ['slug' => $anime->getSlug()]);
            }
        }

        // commentaires publics visibles par tous
        $publicComments = $uaRepo->createQueryBuilder('ua')
            ->andWhere('ua.anime = :anime')
            ->andWhere('ua.isPublic = 1')
            ->andWhere('ua.comment IS NOT NULL AND ua.comment <> \'\'')
            ->setParameter('anime', $anime)
            ->orderBy('ua.updatedAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()->getResult();

        return $this->render('anime/show.html.twig', [
            'anime' => $anime,
            'form' => isset($form) ? $form->createView() : null,
            'publicComments' => $publicComments,
        ]);
    }
}
