<?php

declare(strict_types=1);

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
      '#markup' => '<p>' . $this->t('Current high school year:') . ' ' . ($currentHighSchoolYear ? $currentHighSchoolYear : '-') . '</p>',
    ];

    $form['high_school_year_first'] = [
      '#type' => 'number',
      '#title' => $this->t('Starting year for high school year'),
      '#min' => 2020,
      '#max' => 9999,
      '#default_value' => ($currentHighSchoolYear ? SchoolUtility::splitStartYear($currentHighSchoolYear) : ''),
      '#description' => $this->t('Select the starting year for a high school year period. For example, selecting "2022" would set the school year to "2022-2023".'),
    ];

    $form['current_comprehensive_school_year_info'] = [
      '#markup' => '<p>' . $this->t('Current comprehensive school year:') . ' ' . ($currentComprehensiveSchoolYear ? $currentComprehensiveSchoolYear : '-') . '</p>',
    ];

    $form['comprehensive_school_year_first'] = [
      '#type' => 'number',
      '#title' => $this->t('Starting year for comprehensive school year'),
      '#min' => 2020,
      '#max' => 9999,
      '#default_value' => ($currentComprehensiveSchoolYear ? SchoolUtility::splitStartYear($currentComprehensiveSchoolYear) : ''),
      '#description' => $this->t('Select the starting year for a comprehensive school year period. For example, selecting "2022" would set the school year to "2022-2023".'),
    ];

    $form['additional_schools'] = [
      '#type' => 'textarea',
      '#title' => $this->t('List of additional schools to school index'),
      '#default_value' => implode(',', SchoolUtility::getAdditionalSchools()),
      '#description' => $this->t('Schools should be linked to the correct services in TPR, but in some cases, this is not possible. With this field, TPR units can be marked as schools in the school index by adding their IDs as a comma-separated list. Example: <code>1234,2345,3456</code>. <strong>Note!</strong> Elastic search index must be reindexed if the values are changed.', options: ['context' => 'helfi react search']),
      '#rows' => 2,
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
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    $values = $form_state->getValues();

    // Validate additional schools input.
    if (
      !empty($values['additional_schools']) &&
      !$this->isValidString($values['additional_schools'])
    ) {
      $form_state->setErrorByName('additional_schools',
        $this->t('The list of additional schools must be a comma-separated list of integers')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    SchoolUtility::setCurrentHighSchoolYear(SchoolUtility::composeSchoolYear((int) $form_state->getValue('high_school_year_first')));
    SchoolUtility::setCurrentComprehensiveSchoolYear(SchoolUtility::composeSchoolYear((int) $form_state->getValue('comprehensive_school_year_first')));
    SchoolUtility::setAdditionalSchools($form_state->getValue('additional_schools'));
  }

  /**
   * Validates a comma-separated string.
   *
   * @param string $value
   *   Input string.
   *
   * @return bool
   *   True if input is valid.
   */
  private function isValidString(string $value): bool {
    return $value === '' || preg_match('/^\s*\d+(?:\s*,\s*\d+)*\s*$/', $value);
  }

}
