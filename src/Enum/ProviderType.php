<?php

namespace App\Enum;

enum ProviderType: string
{
  case HounsingHost = "housing_host";
  case ActivityPartner = "activity_partner";
  case EventOrganizer = "event_organizer";
  case Producer = "producer";

  public function toRole(): Role
  {
    return match ($this) {
      self::HounsingHost => Role::Host,
      self::ActivityPartner => Role::ActivityPartner,
      self::Producer => Role::Producer,
      self::EventOrganizer => Role::EventOrganizer,
    };
  }

  public function label(): string
  {
    return match ($this) {
      self::HounsingHost => 'Hébergeur',
      self::ActivityPartner => 'Prestataire d\'activités',
      self::EventOrganizer => 'Professionnel de l\'évenementiel',
      self::Producer => 'Producteur / Artisan',
    };
  }
}