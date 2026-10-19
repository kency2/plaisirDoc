<?php

namespace App\ApiResource;

use Symfony\Component\Validator\Constraints as Assert;

final class ResendVerificationInput
{

  #[Assert\NotBlank]
  #[Assert\Email]
  #[Assert\Length(max:160)]
  public string $email = "";
}