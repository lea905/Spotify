<?php

namespace App\Factory;

use App\Entity\Artist;

class ArtistFactory{
    /**
     * Create a single Artist from Spotify data.
     */
    public static function createFromSpotifyData(array $data): Artist
    {
        $artist = new Artist();
        return $artist
            ->setSpotifyId($data['id'] ?? '')
            ->setName($data['name'] ?? '')
            ->setGenres($data['genres'] ?? [])
            ->setPopularity($data['popularity'] ?? 0)
            ->setFollowers($data['popularity'] ?? 0)
            ->setImageUrl($data['images'][0]['url'] ?? null);
    }

    public static function createMultipleFromSpotifyData(array $artistsData): array
    {
        $artists = [];

        // Iterate over each artist data and create an Artist object
        foreach ($artistsData as $artistData) {
            $artists[] = self::createFromSpotifyData($artistData);
        }

        return $artists;
    }



}