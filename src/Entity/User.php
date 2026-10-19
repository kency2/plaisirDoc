<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Trait\DateTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Table(name: 'users')]
#[HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    use DateTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(name:'password_hash')]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $emailVerifiedAt = null;

    #[ORM\Column(length: 80)]
    private ?string $firstName = null;

    #[ORM\Column(length: 80)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $avatarUrl = null;

    #[ORM\Column(length: 5)]
    private ?string $locale = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $cgvAcceptedAt = null;

    #[ORM\Column]
    private ?bool $marketingOptIn = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $emailVerificationCodeHash = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $emailVerificationExpiresAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $emailVerificationSentAt = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $emailVerificationAttempts = null;

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

    public function getEmailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?\DateTimeImmutable $emailVerifiedAt): static
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

    public function getLastName(): ?string
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

    public function getCgvAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->cgvAcceptedAt;
    }

    public function setCgvAcceptedAt(\DateTimeImmutable $cgvAcceptedAt): static
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

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
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

    public function getEmailVerificationExpiresAt(): ?\DateTimeImmutable
    {
        return $this->emailVerificationExpiresAt;
    }

    public function setEmailVerificationExpiresAt(?\DateTimeImmutable $emailVerificationExpiresAt): static
    {
        $this->emailVerificationExpiresAt = $emailVerificationExpiresAt;

        return $this;
    }

    public function getEmailVerificationSentAt(): ?\DateTimeImmutable
    {
        return $this->emailVerificationSentAt;
    }

    public function setEmailVerificationSentAt(?\DateTimeImmutable $emailVerificationSentAt): static
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
}
