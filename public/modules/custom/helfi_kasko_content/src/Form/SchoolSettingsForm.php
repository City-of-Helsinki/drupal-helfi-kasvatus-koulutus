<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\helfi_kasko_content\SchoolUtility;

/**
 * Change school specific settings, e.g. set the active school year.
 */
class SchoolSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'helfi_kasko_content.school_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $currentHighSchoolYear = SchoolUtility::getCurrentHighSchoolYear();
    $currentComprehensiveSchoolYear = SchoolUtility::getCurrentComprehensiveSchoolYear();

    $form['current_school_year_info'] = [
      '#markup' => '<p>' . t('Current high school year:') . ' ' . ($currentHighSchoolYear ? $currentHighSchoolYear : '-') . '</p>',
    ];

    $form['high_school_year_first'] = [
      '#type' => 'number',
      '#title' => $this->t('Starting year for high school year'),
      '#min' => 2020,
      '#max' => 9999,
      '#default_value' => ($currentHighSchoolYear ? SchoolUtility::splitStartYear($currentHighSchoolYear) : ''),
      '#description' => t('Select the starting year for a high school year period. For example, selecting "2022" would set the school year to "2022-2023".'),
    ];

    $form['current_comprehensive_school_year_info'] = [
      '#markup' => '<p>' . t('Current comprehensive school year:') . ' ' . ($currentComprehensiveSchoolYear ? $currentComprehensiveSchoolYear : '-') . '</p>',
    ];

    $form['comprehensive_school_year_first'] = [
      '#type' => 'number',
      '#title' => $this->t('Starting year for comprehensive school year'),
      '#min' => 2020,
      '#max' => 9999,
      '#default_value' => ($currentComprehensiveSchoolYear ? SchoolUtility::splitStartYear($currentComprehensiveSchoolYear) : ''),
      '#description' => t('Select the starting year for a comprehensive school year period. For example, selecting "2022" would set the school year to "2022-2023".'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    SchoolUtility::setCurrentHighSchoolYear(SchoolUtility::composeSchoolYear((int) $form_state->getValue('high_school_year_first')));
    SchoolUtility::setCurrentComprehensiveSchoolYear(SchoolUtility::composeSchoolYear((int) $form_state->getValue('comprehensive_school_year_first')));
  }

}
