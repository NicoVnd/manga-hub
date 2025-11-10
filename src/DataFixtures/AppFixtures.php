<?php

namespace App\DataFixtures;

use App\Entity\Anime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();

        $items = [
            ['Slam Dunk', 'slam-dunk', 'Un lycéen découvre le basket.', 'https://picsum.photos/seed/slamdunk/400/600', 'FINISHED', 9.2],
            ['Kuroko no Basket', 'kuroko-no-basket', 'Génies du basket au lycée.', 'https://picsum.photos/seed/kuroko/400/600', 'FINISHED', 8.5],
            ['Haikyuu!!', 'haikyuu', 'Volley… mais trop stylé.', 'https://picsum.photos/seed/haikyuu/400/600', 'FINISHED', 9.0],
            ['Blue Lock', 'blue-lock', 'Battle royale du foot.', 'https://picsum.photos/seed/bluelock/400/600', 'ONGOING', 8.1],
            ['Captain Tsubasa', 'captain-tsubasa', 'Le classique du foot.', 'https://picsum.photos/seed/tsubasa/400/600', 'FINISHED', 7.8],
        ];

        foreach ($items as [$title, $slug, $synopsis, $cover, $status, $avg]) {
            $a = (new Anime())
                ->setTitle($title)
                ->setSlug($slug)
                ->setSynopsis($synopsis)
                ->setCoverUrl($cover)
                ->setStatus($status)
                ->setAvgRating($avg)
                ->setCreatedAt($now)
                ->setUpdatedAt(new \DateTime());
            $manager->persist($a);
        }
        $manager->flush();
    }
}
