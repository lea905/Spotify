<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Service\ArtistService;
use App\Service\AuthSpotifyService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/artist')]
class ArtistController extends AbstractController
{
    private string $token;
    public function __construct(
        private readonly AuthSpotifyService $authSpotifyService,
        private readonly ArtistService       $spotifyRequestService
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }

    #[Route('/favoris', name: 'app_artist_favoris')]
    public function favorisShow(EntityManagerInterface  $entityManager): Response{
        $repository = $entityManager->getRepository(Artist::class);
        $artists = $repository->findAll();

        return $this->render('artist/favoris.html.twig', [
            'artists' => $artists,
        ]);
    }

    #[Route('/{search?}', name: 'app_artist_index')]
    public function index(string $search = null): Response
    {
        return $this->render('artist/index.html.twig', [
            'artists' => $this->spotifyRequestService->searchArtists($search ?: "Soprano", $this->token),
            'search' => $search,
        ]);
    }

    #[Route('/show/{id}', name: 'app_artist_show')]
    public function show(string $id): Response
    {
        return $this->render('artist/show.html.twig', [
            'artist' => $this->spotifyRequestService->getArtist($id, $this->token),
        ]);
    }

    #[Route('/save/{id}', name: 'app_artist_save')]
    public function saveSpotifyData(EntityManagerInterface  $entityManager, string $id): Response
    {
        $artists = $this->spotifyRequestService->getArtist($id, $this->token);

        $existingTrack = $entityManager->getRepository(Artist::class)
            ->findOneBy(['spotifyId' => $artists->getSpotifyId()]);

        if ($existingTrack) {
            return $this->json(['message' => 'Cet artiste est déjà enregistré !']);
        }

        $entityManager->persist($artists);
        $entityManager->flush();

        return $this->json(['message' => 'Artiste a été enregistré !']);
    }

    #[Route('/deleteFav/{id}', name: 'app_artist_deleteFav')]
    public function deleteSpotifyData(EntityManagerInterface $entityManager, string $id): Response
    {
        $artist = $entityManager->getRepository(Artist::class)
            ->findOneBy(['spotifyId' => $id]);

        $entityManager->remove($artist);
        $entityManager->flush();

        return $this->json(['message' => 'Artiste a été supprimé des favoris !']);
    }

}