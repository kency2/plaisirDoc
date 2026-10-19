<?php

namespace App\ApiResource;

use Symfony\Component\Validator\Constraints as Assert;

//  Données modifiables par l'equipe
final class ZoneInput
{
  #[Assert\NotBlank]
  #[Assert\Length(max: 120, maxMessage: 'Le nombre de caractère est limité à {{ limit }} caractères')]
  public string $name = '';

  #[Assert\NotBlank]
  public string $description = '';

  #[Assert\Regex('/^-?\d{1,3}\.\d{1,6}$/')]
  #[Assert\NotBlank]
  #[Assert\Range(min: -90, max: 90)]
  public string $centerLatitude = '';

  #[Assert\Regex('/^-?\d{1,3}\.\d{1,6}$/')]
  #[Assert\NotBlank]
  #[Assert\Range(min: -180, max: 180)]
  public string $centerLongitude = '';

  #[Assert\PositiveOrZero]
  public int $displayOrder = 0;
}
