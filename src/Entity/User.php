<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\ApiResource\RegisterInput;
use App\Enum\Role;
use App\State\Processor\RegisterUserProcessor;
use App\Repository\UserRepository;
use App\Trait\DateTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Table(name: 'users')]
#[HasLifecycleCallbacks]
#[ApiResource(
    shortName: 'Utilisateurs',
    description: 'Gestion des utilisateurs',
    operations: [
        new Post(
            uriTemplate: '/auth/register',
            description: 'Création d\'un compte utilisateur',
            name: 'register',
            input: RegisterInput::class,
            processor: RegisterUserProcessor::class,
            status: 201,
            security: null
        ),
        new Get(
            uriTemplate: '/me',
            name: 'me_get',
            security: 'is_granted(IS_AUTHENTICATED_FULLY)',
            // provider: CurrentUserProvider::class
        )
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    use DateTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(name: 'password_hash', length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(nullable: true, name: 'email_verified_at')]
    #[Groups(['user:read'])]
    private ?DateTimeImmutable $emailVerifiedAt = null;

    #[ORM\Column(length: 80, name: 'first_name')]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    private string $firstName;

    #[ORM\Column(length: 80, name: 'last_name')]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\Length(max: 20)]
    private ?string $phone = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\Length(max: 500)]
    #[Assert\Url(requireTld: true)]
    private ?string $avatarUrl = null;

    #[ORM\Column(length: 5)]
    #[Assert\Length(exactly: 5)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $locale = null;

    #[ORM\Column(name: 'cgv_accepted_at')]
    private ?DateTimeImmutable $cgvAcceptedAt = null;

    #[ORM\Column(name: 'marketing_opt_in')]
    #[Groups(['user:read', 'user:write'])]
    private ?bool $marketingOptIn = null;

    #[ORM\Column(name: 'deleted_at', nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    #[ORM\Column(name: 'email_verification_code_hash', length: 64, nullable: true)]
    private ?string $emailVerificationCodeHash = null;

    #[ORM\Column(name: 'email_verification_expires_at', nullable: true)]
    private ?DateTimeImmutable $emailVerificationExpiresAt = null;

    #[ORM\Column(name: 'email_verification_sent_at', nullable: true)]
    private ?DateTimeImmutable $emailVerificationSentAt = null;

    #[ORM\Column(name: 'email_verification_attempts', type: Types::SMALLINT, options: ['default' => 0])]
    private int $emailVerificationAttempts = 0;

    /**
     * @var Collection<int, ProviderProfile>
     */
    #[ORM\OneToMany(targetEntity: ProviderProfile::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $providerProfiles;

    public function __construct(
        string $email,
        string $firstName,
        string $lastName,
        DateTimeImmutable $cgvAcceptedAt,
        Role $initialRole = Role::Traveler,
    ) {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->cgvAcceptedAt = $cgvAcceptedAt;
        $this->roles = [$initialRole->value];
        $this->providerProfiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getEmailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?DateTimeImmutable $emailVerifiedAt): static
    {
        $this->emailVerifiedAt = $emailVerifiedAt;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): static
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLanguage(): string
    {
        return str_starts_with('en', $this->locale) ? 'en' : 'fr';
    }

    public function getCgvAcceptedAt(): ?DateTimeImmutable
    {
        return $this->cgvAcceptedAt;
    }

    public function setCgvAcceptedAt(DateTimeImmutable $cgvAcceptedAt): static
    {
        $this->cgvAcceptedAt = $cgvAcceptedAt;

        return $this;
    }

    public function isMarketingOptIn(): ?bool
    {
        return $this->marketingOptIn;
    }

    public function setMarketingOptIn(bool $marketingOptIn): static
    {
        $this->marketingOptIn = $marketingOptIn;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getEmailVerificationCodeHash(): ?string
    {
        return $this->emailVerificationCodeHash;
    }

    public function setEmailVerificationCodeHash(?string $emailVerificationCodeHash): static
    {
        $this->emailVerificationCodeHash = $emailVerificationCodeHash;

        return $this;
    }

    public function getEmailVerificationExpiresAt(): ?DateTimeImmutable
    {
        return $this->emailVerificationExpiresAt;
    }

    public function setEmailVerificationExpiresAt(?DateTimeImmutable $emailVerificationExpiresAt): static
    {
        $this->emailVerificationExpiresAt = $emailVerificationExpiresAt;

        return $this;
    }

    public function getEmailVerificationSentAt(): ?DateTimeImmutable
    {
        return $this->emailVerificationSentAt;
    }

    public function setEmailVerificationSentAt(?DateTimeImmutable $emailVerificationSentAt): static
    {
        $this->emailVerificationSentAt = $emailVerificationSentAt;

        return $this;
    }

    public function getEmailVerificationAttempts(): ?int
    {
        return $this->emailVerificationAttempts;
    }

    public function setEmailVerificationAttempts(int $emailVerificationAttempts): static
    {
        $this->emailVerificationAttempts = $emailVerificationAttempts;

        return $this;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    /**
     * Nouveau code envoyé : remplace le précedent et remet le compteur d'essais à 0
     */
    public function startEmailVerification(string $codeHash, DateTimeImmutable $expiresAt): static
    {
        $this->emailVerificationCodeHash = $codeHash;
        $this->emailVerificationExpiresAt = $expiresAt;
        $this->emailVerificationSentAt = new DateTimeImmutable();
        $this->emailVerificationAttempts = 0;

        return $this;
    }

    /**
     * @return Collection<int, ProviderProfile>
     */
    public function getProviderProfiles(): Collection
    {
        return $this->providerProfiles;
    }

    public function addProviderProfile(ProviderProfile $providerProfile): static
    {
        if (!$this->providerProfiles->contains($providerProfile)) {
            $this->providerProfiles->add($providerProfile);
            $providerProfile->setUser($this);
        }

        return $this;
    }

    public function removeProviderProfile(ProviderProfile $providerProfile): static
    {
        if ($this->providerProfiles->removeElement($providerProfile)) {
            // set the owning side to null (unless already changed)
            if ($providerProfile->getUser() === $this) {
                $providerProfile->setUser(null);
            }
        }

        return $this;
    }
}
