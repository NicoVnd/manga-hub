<?php

namespace App\Controller;

use App\Entity\Anime;
use App\Form\AnimeType;
use App\Repository\AnimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/')]
final class AnimeController extends AbstractController
{
    #[Route(name: 'app_anime_index', methods: ['GET'])]
    public function index(AnimeRepository $animeRepository): Response
    {
        return $this->render('anime/index.html.twig', [
            'animes' => $animeRepository->findAll(),
        ]);
    }

    

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_anime_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $anime = new Anime();
        $form = $this->createForm(AnimeType::class, $anime);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFileName')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/images/animes',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $anime->setImageFileName($newFilename);
            }

            $entityManager->persist($anime);
            $entityManager->flush();

            return $this->redirectToRoute('app_anime_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('anime/new.html.twig', [
            'anime' => $anime,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_anime_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Anime $anime): Response
    {
        return $this->render('anime/show.html.twig', [
            'anime' => $anime,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_anime_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Anime $anime, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(AnimeType::class, $anime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFileName')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/images/animes',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $anime->setImageFileName($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_anime_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('anime/edit.html.twig', [
            'anime' => $anime,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_anime_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Anime $anime, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$anime->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($anime);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_anime_index', [], Response::HTTP_SEE_OTHER);
    }
}
