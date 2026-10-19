<?php

namespace App\ApiResource;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ["email"], message: 'user.email.already_used', entityClass: User::class)]
final class RegisterInput
{

  #[Assert\Email]
  #[Assert\NotBlank]
  #[Assert\Length(max: 150)]
  public ?string $email = '';

  #[Assert\NotBlank]
  #[Assert\Length(min: 8)]
  public ?string $plainPassword = '';

  #[Assert\NotBlank]
  #[Assert\Length(max: 80)]
  public ?string $firstName = '';

  #[Assert\NotBlank]
  #[Assert\Length(max: 80)]
  public ?string $lastName = '';

  #[Assert\Length(max: 20)]
  public ?string $phone = null;

  #[Assert\Length(exactly: 5)]
  public ?string $locale = 'fr-FR';
  public ?bool $marketingOptIn = false;

  #[Assert\IsTrue(message: 'user.cgv.required')]
  public ?bool $cgvAccepted = false;
}