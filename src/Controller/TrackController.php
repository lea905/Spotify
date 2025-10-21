<?php

namespace App\Controller;

use App\Entity\Track;
use App\Form\TrackType;
use App\Service\AuthSpotifyService;
use App\Service\TrackService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/track')]
class TrackController extends AbstractController
{

    private string $token;

    public function __construct(
        private readonly AuthSpotifyService $authSpotifyService,
        private readonly TrackService       $spotifyRequestService
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }

    #[Route('/favoris', name: 'app_track_favoris')]
    public function favorisShow(EntityManagerInterface  $entityManager): Response{
        $repository = $entityManager->getRepository(Track::class);
        $tracks = $repository->findAll();

        return $this->render('track/favoris.html.twig', [
            'tracks' => $tracks,
        ]);
    }

    #[Route('/{search?}', name: 'app_track_index')]
    public function index(string $search = null): Response
    {

        return $this->render('track/index.html.twig', [
            'tracks' => $this->spotifyRequestService->searchTracks($search ?: "blam's", $this->token),
            'search' => $search,
        ]);
    }


    #[Route('/show/{id}', name: 'app_track_show')]
    public function show(string $id): Response
    {
        $track = $this->spotifyRequestService->getTrack($id, $this->token);

        return $this->render('track/show.html.twig', [
            'track' => $track,
        ]);

    }
    #[Route('/save/{id}', name: 'app_track_save')]
    public function saveSpotifyData(EntityManagerInterface  $entityManager, string $id): Response
    {
        $track = $this->spotifyRequestService->getTrack($id, $this->token);

        $existingTrack = $entityManager->getRepository(Track::class)
            ->findOneBy(['spotifyId' => $track->getSpotifyId()]);

        if ($existingTrack) {
            return $this->json(['message' => 'Ce track est déjà enregistré !']);
        }

        $entityManager->persist($track);
        $entityManager->flush();

        return $this->json(['message' => 'La musique a été enregistré !']);
    }

    #[Route('/deleteFav/{id}', name: 'app_track_deleteFav')]
    public function deleteSpotifyData(EntityManagerInterface $entityManager, string $id): Response
    {
        $track = $entityManager->getRepository(Track::class)
            ->findOneBy(['spotifyId' => $id]);

        $entityManager->remove($track);
        $entityManager->flush();

        return $this->json(['message' => 'La musique a été supprimé des favoris !']);
    }




}
