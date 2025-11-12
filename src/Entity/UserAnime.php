<?php

namespace App\Entity;

use App\Enum\WatchingStatus;
use App\Repository\UserAnimeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAnimeRepository::class)]
#[ORM\Table(name: 'user_anime')]
#[ORM\UniqueConstraint(name: 'uniq_user_anime', columns: ['user_id','anime_id'])]
class UserAnime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tracks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Anime $anime = null;

    // Doctrine sait mapper les enums "backed" en Symfony 6.2+/7
    #[ORM\Column(enumType: WatchingStatus::class, nullable: true)]
    private ?WatchingStatus $status = null;

    #[ORM\Column(nullable: true)]
    private ?int $rating = null; // 1..10

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isPublic = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }

    public function getAnime(): ?Anime { return $this->anime; }
    public function setAnime(Anime $anime): self { $this->anime = $anime; return $this; }

    public function getStatus(): ?WatchingStatus { return $this->status; }
    public function setStatus(?WatchingStatus $status): self { $this->status = $status; return $this; }

    public function getRating(): ?int { return $this->rating; }
    public function setRating(?int $rating): self { $this->rating = $rating; return $this; }

    public function getComment(): ?string { return $this->comment; }
    public function setComment(?string $comment): self { $this->comment = $comment; return $this; }

    public function isPublic(): bool { return $this->isPublic; }
    public function setIsPublic(bool $isPublic): self { $this->isPublic = $isPublic; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }
}
