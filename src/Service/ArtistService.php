<?php

namespace App\Service;

use App\Entity\Artist;
use App\Factory\ArtistFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArtistService{

    public function __construct(private HttpClientInterface $httpClient, private readonly ArtistFactory $artistFactory)
    {}

    public function searchArtists(string $artist, string $token): array
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?query=' . $artist . '&type=artist&locale=fr-FR', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return $this->artistFactory->createMultipleFromSpotifyData($response->toArray()['artists']['items']);
    }

    public function getArtist(string $spotifyId,string $token) : Artist
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/artists/' . $spotifyId, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return $this->artistFactory->createFromSpotifyData($response->toArray());
    }


}