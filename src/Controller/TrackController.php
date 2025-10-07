<?php
namespace App\Controller;

use App\Service\AuthSpotifyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;


#[Route('/track')]
class TrackController extends AbstractController
{

    private string $token;
    private $httpClient;

    public function __construct(private readonly AuthSpotifyService $authSpotifyService, HttpClientInterface $httpClient){
        $this->token = $this->authSpotifyService->auth();
        $this->httpClient = $httpClient;
    }

    #[Route('/', name: 'app_track_index')]
    public function index(Request $request): Response
    {
        $query = $request->query->get('q', 'booba');
        if (empty($query)) {
            $query = 'booba';
        }

        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?query=' . urlencode($query) . '&type=track&local=fr-FR', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ]
        ]);

        $data = json_decode($response->getContent(), true);
        $tracks = $data['tracks']['items'];

        dump($tracks);

        return $this->render('track/index.html.twig', [
            'tracks' => $tracks,
            'query' => $query,
        ]);
    }



    #[Route('/{id}', name: 'app_track_show')]
    public function show(string $id): Response
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/tracks/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ]
        ]);

        $track = json_decode($response->getContent(), true);

        return $this->render('track/show.html.twig', [
            'track' => $track,
        ]);

    }

}
