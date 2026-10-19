<?php

namespace App\ApiResource;

use Symfony\Component\Validator\Constraints as Assert;

final class ResetPasswordInput
{
  /**
   * Jeton reçu dans le lien de l'e-mail (`/reset-password?token=...` côté front).
   */
  #[Assert\NotBlank]
  #[Assert\Length(max: 100)]
  public string $token = '';

  // Mêmes règles qu'à l'inscription (RegisterInput).
  #[Assert\NotBlank]
  #[Assert\Length(min: 8)]
  public string $plainPassword = '';
}