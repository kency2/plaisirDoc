<?php

namespace App\Entity;

use App\Repository\ProviderProfileRepository;
use App\Trait\DateTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ProviderProfileRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'provider_profiles')]
class ProviderProfile
{

    use DateTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'providerProfiles')]
    private ?User $user = null;

    #[ORM\Column(length: 30)]
    private ?string $providerType = null;

    #[ORM\Column(length: 120)]
    private ?string $displayName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $companyName = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(nullable: true)]
    private ?int $responseTimeMinutes = null;

    #[ORM\Column]
    private ?bool $verified = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $payoutDetails = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $memberSince = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $commissionRate = null;

    #[ORM\Column]
    private ?bool $isPrimary = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getProviderType(): ?string
    {
        return $this->providerType;
    }

    public function setProviderType(string $providerType): static
    {
        $this->providerType = $providerType;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getResponseTimeMinutes(): ?int
    {
        return $this->responseTimeMinutes;
    }

    public function setResponseTimeMinutes(?int $responseTimeMinutes): static
    {
        $this->responseTimeMinutes = $responseTimeMinutes;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): static
    {
        $this->verified = $verified;

        return $this;
    }

    public function getPayoutDetails(): ?string
    {
        return $this->payoutDetails;
    }

    public function setPayoutDetails(?string $payoutDetails): static
    {
        $this->payoutDetails = $payoutDetails;

        return $this;
    }

    public function getMemberSince(): ?\DateTimeImmutable
    {
        return $this->memberSince;
    }

    public function setMemberSince(\DateTimeImmutable $memberSince): static
    {
        $this->memberSince = $memberSince;

        return $this;
    }

    public function getCommissionRate(): ?string
    {
        return $this->commissionRate;
    }

    public function setCommissionRate(?string $commissionRate): static
    {
        $this->commissionRate = $commissionRate;

        return $this;
    }

    public function isPrimary(): ?bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(bool $isPrimary): static
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }
}
