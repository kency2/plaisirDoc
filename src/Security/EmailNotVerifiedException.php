<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

/**
 * Connexion refusé, l'adresse email du compte n'a pas encore été validé
 */
final class EmailNotVerifiedException extends CustomUserMessageAccountStatusException
{

  public function __construct()
  {
    parent::__construct('auth.email_not_verified');
  }
}
