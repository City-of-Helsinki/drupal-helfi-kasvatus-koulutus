<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content;

/**
 * Contains helper functions for school addons.
 */
class SchoolUtility {

  /**
   * School year key used with State API and additional schools.
   */
  private const HIGH_SCHOOL_YEAR_KEY = 'helfi_kasko_content.high_school_year';
  private const COMPREHENSIVE_SCHOOL_YEAR_KEY = 'helfi_kasko_content.comprehensive_school_year';
  private const ADDITIONAL_SCHOOLS = 'helfi_kasko_content.additional_schools_to_school_index';

  /**
   * Helper function to get the current high school year.
   *
   * @return string|null
   *   The current school year, e.g. "2022-2023".
   */
  public static function getCurrentHighSchoolYear(): ?string {
    return \Drupal::state()->get(self::HIGH_SCHOOL_YEAR_KEY);
  }

  /**
   * Helper function to get the current comprehensive school year.
   *
   * @return string|null
   *   The current school year, e.g. "2022-2023".
   */
  public static function getCurrentComprehensiveSchoolYear(): ?string {
    return \Drupal::state()->get(self::COMPREHENSIVE_SCHOOL_YEAR_KEY);
  }

  /**
   * Helper function to get the additional schools.
   *
   * @return array|null
   *   The school IDs as an array.
   */
  public static function getAdditionalSchools(): ?array {
    $schools = \Drupal::state()->get(self::ADDITIONAL_SCHOOLS);

    if (empty($schools)) {
      return NULL;
    }

    // Explode the string into an array and trim each element.
    return array_map('trim', explode(',', $schools));
  }

  /**
   * Helper function to set the current high school year.
   *
   * @param string $schoolYear
   *   The current school year, e.g. "2022-2023".
   */
  public static function setCurrentHighSchoolYear(string $schoolYear) {
    \Drupal::state()->set(self::HIGH_SCHOOL_YEAR_KEY, $schoolYear);
  }

  /**
   * Helper function to set the comprehensive current school year.
   *
   * @param string $schoolYear
   *   The current school year, e.g. "2022-2023".
   */
  public static function setCurrentComprehensiveSchoolYear(string $schoolYear) {
    \Drupal::state()->set(self::COMPREHENSIVE_SCHOOL_YEAR_KEY, $schoolYear);
  }

  /**
   * Helper function to set the additional schools to school index.
   *
   * @param string $additionalSchools
   *   A comma separated list of additional schools, e.g. "1234,2345,3456".
   */
  public static function setAdditionalSchools(string $additionalSchools) {
    \Drupal::state()->set(self::ADDITIONAL_SCHOOLS, $additionalSchools);
  }

  /**
   * Gets the school year from a starting year.
   *
   * @param int $firstYear
   *   The year.
   *
   * @return string
   *   The school year, e.g. '2022-2023'.
   */
  public static function composeSchoolYear(int $firstYear): string {
    return $firstYear . '-' . strval($firstYear + 1);
  }

  /**
   * Gets the start year from a school year period.
   *
   * @param string $schoolYear
   *   The school year, e.g. '2022-2023'.
   *
   * @return string
   *   The year.
   */
  public static function splitStartYear(string $schoolYear): string {
    return strtok($schoolYear, '-');
  }

}
