<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\ChangePasswordInput;
use App\ApiResource\ForgotPasswordInput;
use App\ApiResource\RegisterInput;
use App\ApiResource\ResendVerificationInput;
use App\ApiResource\ResetPasswordInput;
use App\ApiResource\VerifyEmailInput;
use App\Enum\Role;
use App\State\Processor\AnonymizeUserProcessor;
use App\State\Processor\ChangePasswordProcessor;
use App\State\Processor\ForgotPasswordProcessor;
use App\State\Processor\RegisterUserProcessor;
use App\Repository\UserRepository;
use App\State\Processor\ResendVerificationProcessor;
use App\State\Processor\ResetPasswordProcessor;
use App\State\Processor\VerifyEmailProcessor;
use App\State\Provider\CurrentUserProvider;
use App\Trait\DateTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
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
        new Post(
            uriTemplate: '/auth/email/verify',
            status: 204,
            name: 'email_verify',
            description: 'Activation du compte : code à 6 chiffres envoyé par email à l\'inscription',
            input: VerifyEmailInput::class,
            output: false,
            processor: VerifyEmailProcessor::class,
            security: null
        ),
        new Post(
            uriTemplate: '/auth/email/resend',
            status: 202,
            description: 'Renvoyer le code de verification par mail',
            name: 'email_resend',
            input: ResendVerificationInput::class,
            output: false,
            security: null,
            processor: ResendVerificationProcessor::class
        ),
        new Post(
            uriTemplate: '/auth/password/forgot',
            name: 'password_forgot',
            description: 'Envoyer un mail de réinitalisation de mot de passe',
            status: 202,
            input: ForgotPasswordInput::class,
            output: false,
            processor: ForgotPasswordProcessor::class,
            security: null
        ),
        new Post(
            uriTemplate: '/auth/password/reset',
            name: 'password_reset',
            description: 'Mise à jour du mot de passe',
            status: 204,
            input: ResetPasswordInput::class,
            output: false,
            security: null,
            processor: ResetPasswordProcessor::class
        ),
        new Get(
            uriTemplate: '/me',
            name: 'me_get',
            description: 'information sur l\'utilisateur connecté',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            provider: CurrentUserProvider::class,
        ),
        new Patch(
            uriTemplate: '/me',
            name: 'me_patch',
            description: 'Mise à jour de son profil',
            read: true,
            provider: CurrentUserProvider::class,
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('IS_AUTHENTICATED_FULLY')"
        ),
        new Post(
            uriTemplate: '/me/password',
            name: 'me_password',
            description: 'Modifier son mot de passe depuis son profil',
            status: 204,
            input: ChangePasswordInput::class,
            output: false,
            processor: ChangePasswordProcessor::class,
            security: 'is_granted("IS_AUTHENTICATED_FULLY")'
        ),
        new Delete(
            uriTemplate: '/me',
            name: 'me_delete',
            description: 'Supprimer son compte',
            read: true,
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            provider: CurrentUserProvider::class,
            processor: AnonymizeUserProcessor::class
        )
    ],
    normalizationContext: ['groups' => ['user:read']]
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

    public function markEmailVerified(): static
    {
        $this->emailVerifiedAt = new DateTimeImmutable();
        $this->emailVerificationCodeHash = null;
        $this->emailVerificationExpiresAt = null;
        $this->emailVerificationAttempts = 0;

        return $this;
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

    public function recordFailedEmailVerification(): static
    {
        ++$this->emailVerificationAttempts;

        return $this;
    }

    public function setEmailVerificationAttempts(int $emailVerificationAttempts): static
    {
        $this->emailVerificationAttempts = $emailVerificationAttempts;

        return $this;
    }

    public function isEmailVerified(): bool
    {
        return null !== $this->emailVerifiedAt;
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
