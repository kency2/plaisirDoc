<?php

namespace App\Enum;

enum Role: string
{
  case Traveler         = 'traveler';
  case Host             = 'host';
  case ActivityPartner  = 'activity_partner';
  case EventOrganizer   = 'event_organizer';
  case Producer         = 'producer';
  case Admin            = 'admin';

  public function toSecurityRole(): string
  {
    return 'ROLE_' . strtoupper($this->value);
  }
}