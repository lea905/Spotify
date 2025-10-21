<?php

namespace App\Controller;

use App\Service\ArtistService;
use App\Service\AuthSpotifyService;
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

    #[Route('/{search?}', name: 'app_artist_index')]
    public function index(string $search = null): Response
    {
        return $this->render('artist/index.html.twig', [
            'artists' => $this->spotifyRequestService->searchArtists($search ?: "eminem", $this->token),
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

}