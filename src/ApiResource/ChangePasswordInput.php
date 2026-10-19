<?php

namespace App\ApiResource;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangePasswordInput
{

  #[Assert\NotBlank]
  #[SecurityAssert\UserPassword(message: "user.current_password.invalid")]
  public string $currentPassword = "";

  #[Assert\NotBlank]
  #[Assert\Length(min: 8)]
  #[Assert\NotEqualTo(propertyPath:'currentPassword', message: 'user.new_password.same')]
  public string $newPassword = "";
}