<?php

namespace App\Service;

use App\Entity\Artist;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArtistService{

    public function __construct(private HttpClientInterface $httpClient)
    {}

    public function searchArtists(string $artist, string $token): array
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?query=' . $artist . '&type=artist&locale=fr-FR', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $artists = [];

        foreach ($response->toArray()['artists']['items'] as $artist) {
            $artists[] = new Artist($artist);
        }

        return $artists;
    }

    public function getArtist(string $spotifyId,string $token) : Artist
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/artists/' . $spotifyId, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        $artist = new Artist($response->toArray());

        return $artist;
    }


}