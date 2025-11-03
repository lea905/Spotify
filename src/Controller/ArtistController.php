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

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        return $this->render('artist/favoris.html.twig', [
            'artists' => $user->getFavoriteArtists(),
        ]);
    }

    #[Route('/{search?}', name: 'app_artist_index')]
    public function index(string $search = null): Response
    {
        return $this->render('artist/index.html.twig', [
            'artists' => $this->spotifyRequestService->searchArtists($search ?: "Lady gaga", $this->token),
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
    public function saveSpotifyData(EntityManagerInterface $entityManager, string $id): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $artist = $this->spotifyRequestService->getArtist($id, $this->token);

        $existingArtist = $entityManager->getRepository(Artist::class)
            ->findOneBy(['spotifyId' => $artist->getSpotifyId()]);

        if (!$existingArtist) {
            $entityManager->persist($artist);
            $entityManager->flush();
            $existingArtist = $artist;
        }

        if ($user->getFavoriteArtists()->contains($existingArtist)) {
            return $this->json(['message' => 'Cet artiste est déjà dans vos favoris !']);
        }

        $user->addFavoriteArtist($existingArtist);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'Artiste ajouté à vos favoris !']);
    }


    #[Route('/deleteFav/{id}', name: 'app_artist_deleteFav')]
    public function deleteSpotifyData(EntityManagerInterface $entityManager, string $id): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $artist = $entityManager->getRepository(Artist::class)
            ->findOneBy(['spotifyId' => $id]);

        if (!$artist) {
            return $this->json(['error' => 'Artiste non trouvé'], 404);
        }

        $user->removeFavoriteArtist($artist);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'Artiste supprimé des favoris !']);
    }


}