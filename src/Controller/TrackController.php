<?php
namespace App\Controller;

use App\Entity\Track;
use App\Form\TrackType;
use App\Service\AuthSpotifyService;
use App\Service\TrackService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;


#[Route('/track')]
class TrackController extends AbstractController
{

    private string $token;
    public function __construct(
        private readonly AuthSpotifyService    $authSpotifyService,
        private readonly TrackService $spotifyRequestService
    ){
        $this->token = $this->authSpotifyService->auth();
    }

    #[Route('/{search?}', name: 'app_track_index')]
    public function index(string $search = null ): Response
    {

        return $this->render('track/index.html.twig', [
            'tracks' => $this->spotifyRequestService->searchTracks($search ?: "kazzey", $this->token),
            'search' => $search,
        ]);
    }



    #[Route('/show/{id}', name: 'app_track_show')]
    public function show(string $id): Response
    {
        return $this->render('track/show.html.twig', [
            'track' => $this->spotifyRequestService->getTrack($id, $this->token),
        ]);

    }

}
