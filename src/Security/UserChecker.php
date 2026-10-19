<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Appelé par le firewall à chaque authentification (login, refresh, JWT) : un compte dont l'adresse
 * e-mail n'est pas vérifiée ne peut pas se connecter (double authentification).
 *
 * Contrôle fait **après** la vérification du mot de passe (`checkPostAuth`) : avec un mauvais mot
 * de passe, la réponse reste « identifiants invalides », sans révéler que le compte existe.
 */
class UserChecker implements UserCheckerInterface
{
  public function checkPreAuth(UserInterface $user): void {}

  public function checkPostAuth(UserInterface $user): void
  {
    if ($user instanceof User && $user->isEmailVerified()) {
      throw new EmailNotVerifiedException();
    }
  }
}
