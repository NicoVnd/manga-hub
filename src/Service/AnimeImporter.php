<?php
namespace App\Service;

use App\Entity\Anime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AnimeImporter
{
    public function __construct(
        private HttpClientInterface $http,
        private EntityManagerInterface $em,
        private SluggerInterface $slugger,   // 👈 ajoute ça

    ) {
    }

    /**
     * Importe N animés depuis Jikan (MyAnimeList) avec pagination simple.
     * @param int $limit nombre d’entrées à importer
     */
    public function importFromJikan(int $limit = 100): int
    {
        $perPage = 25;
        $inserted = 0;
        for ($page = 1; $inserted < $limit; $page++) {
            $resp = $this->http->request('GET', 'https://api.jikan.moe/v4/anime', [
                'query' => ['page' => $page, 'limit' => min($perPage, $limit - $inserted)],
            ]);
            $data = $resp->toArray(false)['data'] ?? [];

            foreach ($data as $row) {
                $extId = (string) ($row['mal_id'] ?? null);
                if (!$extId) {
                    continue;
                }

                $anime = $this->em->getRepository(Anime::class)->findOneBy(['externalId' => $extId]);
                if (!$anime) {
                    $anime = new Anime();
                }

                $anime
                    ->setExternalId($extId)
                    ->setSource('JIKAN')
                    ->setTitle($row['title'] ?? 'Untitled')
                    ->setSlug($this->slugify($row['title'] ?? ('anime-' . $extId)))
                    ->setSynopsis($row['synopsis'] ?? '')
                    ->setCoverUrl($row['images']['jpg']['image_url'] ?? null)
                    ->setStatus($this->mapStatus($row['status'] ?? ''))
                    ->setUpdatedAt(new \DateTime());

                if (!$anime->getCreatedAt()) {
                    $anime->setCreatedAt(new \DateTimeImmutable());
                }

                $this->em->persist($anime);
                $inserted++;
                if ($inserted >= $limit) {
                    break;
                }
            }
            $this->em->flush();

            if (empty($data)) {
                break;
            } // fin des pages
            usleep(300000); // 300ms pour être gentil avec le rate limit
        }
        return $inserted;
    }

   private function slugify(string $title): string
{
    $title = trim($title);
    if ($title === '') {
        return 'anime-'.bin2hex(random_bytes(4));
    }
    // Slugger gère l’UTF-8, accents, kanji => ASCII “safe”
    $slug = strtolower($this->slugger->slug($title)->toString());
    // filet de sécurité
    if ($slug === '' || $slug === '-') {
        $slug = 'anime-'.bin2hex(random_bytes(4));
    }
    return $slug;
}


    private function mapStatus(string $apiStatus): string
    {
        // Adapte selon ta convention interne (par ex: ONGOING/FINISHED)
        $apiStatus = strtoupper($apiStatus);
        return match (true) {
            str_contains($apiStatus, 'ONGOING'), str_contains($apiStatus, 'AIRING') => 'ONGOING',
            str_contains($apiStatus, 'FINISHED'), str_contains($apiStatus, 'COMPLETE') => 'FINISHED',
            default => 'UNKNOWN',
        };
    }
}
