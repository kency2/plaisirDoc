<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Erreur métier renvoyée au client
 *
 * Le message est une clé de `translations/errors.{fr,en}.yaml` (+ paramètres `%nom%`), traduite
 * dans la langue de la requête (`Accept-Language`) par `TranslateApiExceptionListener`.
 *
 *     throw ApiException::notFound('order.not_found');
 *     throw ApiException::unprocessable('stay.nights_out_of_range', ['min' => 2, 'max' => 7]);
 */
class ApiException extends HttpException
{

  public function __construct(
    int $statusCode,
    private readonly string $key,
    private readonly array $params = []
  ) {
    parent::__construct($statusCode, $key);
  }

  public static function notFound(string $key, array $params = []): self
  {
    return new self(404, $key, $params);
  }

  public static function forbidden(string $key, array $params = []): self
  {
    return new self(403, $key, $params);
  }

  public static function conflict(string $key, array $params = []): self
  {
    return new self(409, $key, $params);
  }

  public static function unprocessable(string $key, array $params = []): self
  {
    return new self(422, $key, $params);
  }

  public function getKey(): string
  {
    return $this->key;
  }

  public function translate(TranslatorInterface $translatorInterface): void
  {
    $params = [];
    foreach ($this->params as $name => $value) {
      $params["%$name%"] = $value;
    }

    $this->message = $translatorInterface->trans($this->message, $params, 'errors');
  }
}