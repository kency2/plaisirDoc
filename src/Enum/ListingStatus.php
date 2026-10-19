<?php

namespace App\Enum;

enum ListingStatus: string
{
  case Draft          = 'draft';
  case PendingReview  = 'pending_review';
  case Published      = 'published';
  case Suspended      = 'suspended';
  case Archived       = 'archived';
}
