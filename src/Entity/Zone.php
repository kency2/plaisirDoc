<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ZoneRepository;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\State\Provider\ZoneProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
#[ORM\Table(name: 'zones', schema: 'plaisir_doc')]
#[ApiResource(
    description: 'Zone géographique',
    operations: [
        new GetCollection(provider: ZoneProvider::class),
        new Get(
            uriTemplate: '/zones/{slug}',
            uriVariables: ['slug'],
            provider: ZoneProvider::class,
            normalizationContext: ['groups' => 'zone:read'],
            description: 'Afficher les informations pour une zone donnée'
        )
    ]

)]
class Zone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Groups(['zone:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 120)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 9, scale: 6)]
    private ?string $centerLatitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 9, scale: 6)]
    private ?string $centerLongitude = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $coverImageUrl = null;

    #[ORM\Column]
    private ?int $displayedOrder = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    private array $counts = ['listings' => 0, 'activities' => 0, 'events' => 0];

    public function __construct(
        string $name,
        string $slug,
        string $description,
        string $centerLatitude,
        string $centerLongitude,
        ?string $coverImageUrl = null
    ) {
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->centerLatitude = $centerLatitude;
        $this->centerLongitude = $centerLongitude;
        $this->coverImageUrl = $coverImageUrl;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCenterLatitude(): ?string
    {
        return $this->centerLatitude;
    }

    public function setCenterLatitude(string $centerLatitude): static
    {
        $this->centerLatitude = $centerLatitude;

        return $this;
    }

    public function getCenterLongitude(): ?string
    {
        return $this->centerLongitude;
    }

    public function setCenterLongitude(string $centerLongitude): static
    {
        $this->centerLongitude = $centerLongitude;

        return $this;
    }

    public function getCoverImageUrl(): ?string
    {
        return $this->coverImageUrl;
    }

    public function setCoverImageUrl(?string $coverImageUrl): static
    {
        $this->coverImageUrl = $coverImageUrl;

        return $this;
    }

    public function getDisplayedOrder(): ?int
    {
        return $this->displayedOrder;
    }

    public function setDisplayedOrder(int $displayedOrder): static
    {
        $this->displayedOrder = $displayedOrder;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     *
     * @return array{listings: int, activities: int, events: int}
     */
    public function getCounts(): array
    {
        return $this->counts;
    }

    /**
     * Undocumented function
     *
     * @param array{listings: int, activities: int, events: int} $counts
     * @return static
     */
    public function setCounts(array $counts): static
    {
        $this->counts = $counts;
        return $this;
    }
}
