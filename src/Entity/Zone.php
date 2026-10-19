<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\PhotoUploadInput;
use App\ApiResource\ZoneInput;
use App\State\Processor\CreateZoneProcessor;
use App\State\Processor\UploadZoneCoverProcessor;
use App\State\Provider\ZoneProvider;
use App\Trait\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
#[ORM\Table(name: 'zones', schema: 'plaisir_doc')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    description: 'Zone géographique',
    operations: [
        new GetCollection(provider: ZoneProvider::class),
        new Get(
            uriTemplate: '/zone/{slug}',
            uriVariables: ['slug'],
            provider: ZoneProvider::class,
            normalizationContext: ['groups' => 'zone:read'],
            description: 'Afficher les informations pour une zone donnée'
        ),
        new Post(
            uriTemplate: '/admin/zone',
            input: ZoneInput::class,
            processor: CreateZoneProcessor::class,
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Patch(
            uriTemplate: '/admin/zone/{slug}',
            uriVariables: ['slug'],
            denormalizationContext: ['groups' => ['zone:write']],
            inputFormats: ['multipart' => ['application/merge-patch+json']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Post(
            description: "Image de couverture",
            uriTemplate: '/admin/zone/{slug}/cover',
            inputFormats: ['multipart' => ['multipart/form-data']],
            input: PhotoUploadInput::class,
            processor: UploadZoneCoverProcessor::class,
            status: 200,
            security: "is_granted('ROLE_ADMIN')"
        )
    ],
    normalizationContext: ['groups' => ['zone:read']],
)]
class Zone
{

    use DateTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Groups(['zone:read', 'zone:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120, maxMessage: 'Le nombre de caractère est limité à {{ limit }} caractères')]
    private string $name;

    #[ORM\Column(length: 120, unique: true)]
    #[ApiProperty(identifier: true)]
    #[Groups(['zone:read'])]
    private string $slug;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['zone:read', 'zone:write'])]
    #[Assert\NotBlank]
    private string $description;

    #[ORM\Column(name: 'center_latitude', type: Types::DECIMAL, precision: 9, scale: 6)]
    #[Groups(['zone:write'])]
    #[Assert\Regex('/^-?\d{1,3}\.\d{1,6}$/')]
    #[Assert\Range(min: -90, max: 90)]
    private string $centerLatitude;

    #[ORM\Column(name: 'center_longitude', type: Types::DECIMAL, precision: 9, scale: 6)]
    #[Groups(['zone:write'])]
    #[Assert\Regex('/^-?\d{1,3}\.\d{1,6}$/')]
    #[Assert\Range(min: -180, max: 180)]
    private string $centerLongitude;

    #[ORM\Column(name: 'cover_image_url', length: 500, nullable: true)]
    #[Groups(['zone:read'])]
    private ?string $coverImageUrl = null;

    #[ORM\Column(name: 'display_order')]
    #[Groups(['zone:read', 'zone:write'])]
    #[Assert\PositiveOrZero]
    private int $displayOrder = 0;

    //  Nombre d'offres publié dans une zone. Non mappé, appelé par ZoneProvider
    private array $counts = ['listings' => 0, 'activities' => 0, 'events' => 0];

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'zone')]
    private Collection $activities;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'zone')]
    private Collection $events;

    /**
     * @var Collection<int, Listing>
     */
    #[ORM\OneToMany(targetEntity: Listing::class, mappedBy: 'zone')]
    private Collection $listings;

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
        $this->activities = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->listings = new ArrayCollection();
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

    #[Groups(['zone:read'])]
    #[SerializedName('centerLongitude')]
    public function getCenterLongitudeValue(): float
    {
        return (float) $this->centerLongitude;
    }

    #[Groups(['zone:read'])]
    #[SerializedName('centerLatitude')]
    public function getCenterLatitudeValue(): float
    {
        return (float) $this->centerLatitude;
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

    public function getDisplayOrder(): ?int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): static
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    /**
     *
     * @return array{listings: int, activities: int, events: int}
     */
    #[Groups(['zone:read'])]
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

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
            $activity->setZone($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getZone() === $this) {
                $activity->setZone(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setZone($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getZone() === $this) {
                $event->setZone(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Listing>
     */
    public function getListings(): Collection
    {
        return $this->listings;
    }

    public function addListing(Listing $listing): static
    {
        if (!$this->listings->contains($listing)) {
            $this->listings->add($listing);
            $listing->setZone($this);
        }

        return $this;
    }

    public function removeListing(Listing $listing): static
    {
        if ($this->listings->removeElement($listing)) {
            // set the owning side to null (unless already changed)
            if ($listing->getZone() === $this) {
                $listing->setZone(null);
            }
        }

        return $this;
    }
}
