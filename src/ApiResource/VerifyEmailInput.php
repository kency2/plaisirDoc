<?php

namespace App\ApiResource;

use Symfony\Component\Validator\Constraints as Assert;

class VerifyEmailInput
{

  #[Assert\NotBlank]
  #[Assert\Email]
  #[Assert\Length(max: 60)]
  public string $email = '';

  #[Assert\NotBlank]
  #[Assert\Regex('/^\d{6}$/', message: 'email_verification.code.invalid_format')]
  public string $code = '';
}