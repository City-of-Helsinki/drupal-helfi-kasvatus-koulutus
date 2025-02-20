<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel\Form;

use Drupal\Core\Form\FormState;
use Drupal\helfi_kasko_content\SchoolUtility;
use Drupal\KernelTests\KernelTestBase;
use Drupal\helfi_kasko_content\Form\SchoolSettingsForm;

/**
 * Tests the SchoolSettingsForm form.
 *
 * @group helfi_kasko_content
 */
class SchoolSettingsFormTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_kasko_content',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['system']);
  }

  /**
   * Tests form validation for valid and invalid JSON.
   */
  public function testValidateForm(): void {
    $form = SchoolSettingsForm::create($this->container);
    $empty_form = [];

    $fields = [
      'high_school_year_first',
      'comprehensive_school_year_first',
    ];

    foreach ($fields as $field) {
      // Test valid values.
      $valid_form_state = new FormState();
      $valid_form_state->setValues([$field => 2025]);
      $form->validateForm($empty_form, $valid_form_state);
      $this->assertFalse($valid_form_state->hasAnyErrors(), 'Form validation should pass with a valid value.');
    }

    // Test additional schools validation with valid cases.
    $valid_values = [
      '',
      '123',
      '123, 456',
      '123, 456, 789',
      ' 123 , 456 , 789 ',
    ];
    foreach ($valid_values as $value) {
      $valid__form_state = new FormState();
      $valid__form_state->setValues(['additional_schools' => $value]);
      $form->validateForm($empty_form, $valid__form_state);
      $this->assertFalse($valid__form_state->hasAnyErrors(), 'Form validation should pass.');
    }

    // Test additional schools validation with invalid cases.
    $invalid_values = [
      '123,, 456',
      '123a, 456',
      '123, ',
      ', 123',
      '123, 456, 789,',
    ];

    foreach ($invalid_values as $invalid_value) {
      $valid__form_state = new FormState();
      $valid__form_state->setValues(['additional_schools' => $invalid_value]);
      $form->validateForm($empty_form, $valid__form_state);
      $this->assertTrue($valid__form_state->hasAnyErrors(), 'Form validation should not pass.');
    }
  }

  /**
   * Tests form submission.
   */
  public function testFormSubmission(): void {
    $form_object = SchoolSettingsForm::create($this->container);
    $form_state = new FormState();

    // Simulate form submission values.
    $form_state->setValues([
      'high_school_year_first' => '2025-2026',
      'comprehensive_school_year_first' => '2025-2026',
      'additional_schools' => '123, 3456, 5677',
    ]);

    // Build, process and submit the form.
    $form = [];
    $form_object->buildForm($form, $form_state);
    $form_object->submitForm($form, $form_state);

    // Assert that the config values are correctly saved.
    $state = $this->container->get('state');
    $values = $form_state->getValues();

    $this->assertEquals($state->get(SchoolUtility::HIGH_SCHOOL_YEAR_KEY), $values['high_school_year_first']);
    $this->assertEquals($state->get(SchoolUtility::COMPREHENSIVE_SCHOOL_YEAR_KEY), $values['comprehensive_school_year_first']);
    $this->assertEquals($state->get(SchoolUtility::ADDITIONAL_SCHOOLS), $values['additional_schools']);
  }

}
