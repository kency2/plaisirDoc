<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Summary of EmailSender
 */
final class EmailSender
{
  public function __construct(
    private readonly MailerInterface $mailer,
    private readonly TranslatorInterface $translator,
    private readonly Environment $twig,
    #[Autowire('%env(FRONT_URL)%')]
    private readonly string $frontUrl
  ) {
  }

  /**
   * @param User $recipiant
   * @param string $key
   * @param array $params   parametre des traductions (%nom% dans les textes)
   * @param string|null $link chemin du front (/order/12) ou url externe du bouton   
   * @param array $context données supplémentaires pour le gabarit
   * @return void
   */
  public function send(User $recipiant, string $key, array $params = [], ?string $link = null, array $context = []): void
  {
    //  Compte anonymisé: adresse factice en '.invalid', rien à envoyer
    if ($recipiant->getDeletedAt() !== null) {
      return;
    }

    $locale = $recipiant->getLanguage();
    $translationParams = self::toTranslationParams($params + ['firstName' => $recipiant->getFirstName()]);

    $template = sprintf('emails/%s.html.twig', $key);
    if (!$this->twig->getLoader()->exists($template)) {
      $template = 'emails/notification.html.twig';
    }

    $email = (new TemplatedEmail())
      ->to(
        new Address(
          $recipiant->getEmail(),
          $recipiant->getFirstName() . ' ' . $recipiant->getLastName()
        )
      )
      ->subject($this->translator->trans($key . '.subject', $translationParams, 'emails', $locale))
      ->htmlTemplate($template)
      ->locale($locale)
      ->context($context + [
        'key' => $key,
        'lang' => $locale,
        'params' => $translationParams,
        'link' => $link !== null ? $this->frontUrl($link) : null,
        'frontUrl' => rtrim($this->frontUrl, '/')
      ]);

    $this->mailer->send($email);
  }

  /**
   * Chemin du front -> URL absolue. Une URL deja absolue (site externe d'un partenaire) est gardée
   * @param string $link
   * @return string
   */
  public function frontUrl(string $link): string
  {
    if (preg_match('#^https?://#', $link)) {
      return $link;
    }

    return rtrim($this->frontUrl, '/') . '/' . ltrim($link, '/');
  }

  public static function toTranslationParams(array $params): array
  {
    $translationParams = [];
    foreach ($params as $name => $value) {
      $translationParams['%' . $name . '%'] = $value;
    }

    return $translationParams;
  }
}
