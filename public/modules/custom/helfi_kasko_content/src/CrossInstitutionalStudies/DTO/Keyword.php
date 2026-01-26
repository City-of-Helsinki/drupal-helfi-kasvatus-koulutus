<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO;

/**
 * Course keywords.
 */
enum Keyword: string {

  case ContactLearning = 'https://linkedevents.api.test.hel.ninja/v1/keyword/helsinki:contact_learning/';
  case GeneralUpperSecondarySchool = 'https://linkedevents.api.test.hel.ninja/v1/keyword/helsinki:general_upper_secondary_school/';
  case HybridLearning = 'https://linkedevents.api.test.hel.ninja/v1/keyword/helsinki:hybrid_learning/';
  case SecondarySchoolCrossInstitutionalStudies = 'https://linkedevents.api.test.hel.ninja/v1/keyword/helsinki:secondary_schools_cross_institutional_studies/';

}
