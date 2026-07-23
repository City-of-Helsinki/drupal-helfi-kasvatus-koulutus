<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Hook;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Views hook implementations for Helfi KASKO Content.
 */
class ViewsHooks {

  use StringTranslationTrait;

  public function __construct(
    private readonly LanguageManagerInterface $languageManager,
    #[Autowire(service: 'cache.default')]
    private readonly CacheBackendInterface $cache,
  ) {
  }

  /**
   * Implements hook_views_post_execute().
   *
   * @see self::buildDefaultsAlter
   */
  #[Hook('views_post_execute')]
  public function viewsPostExecute(ViewExecutable $view): void {
    if ($view->id() !== 'after_school_activity_search') {
      return;
    }

    $current_language = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

    // Remove these strings from TPR unit titles.
    $removableStrings = [
      'Iltapäivätoiminta /',
      'Eftermiddagsverksamhet /',
      'Finskspråkig eftermiddagsverksamhet /',
      'After-school activities /',
    ];

    foreach ($view->result as &$row) {
      if ($row->_entity->hasTranslation($current_language)) {
        $translatedEntity = $row->_entity->getTranslation($current_language);
        $translatedEntity->set('name', trim(str_replace($removableStrings, '', $translatedEntity->get('name')->getString())));
      }
      else {
        $row->_entity->set('name', trim(str_replace($removableStrings, '', $row->_entity->get('name')->getString())));
      }
    }

    // Sort alphabetically based on parsed title.
    if ($current_language === 'en' || $current_language === 'sv') {
      uasort($view->result, fn($a, $b) => $a->_entity->getTranslation($current_language)->get('name')->getString() <=> $b->_entity->getTranslation($current_language)->get('name')->getString());
    }
    else {
      uasort($view->result, fn($a, $b) => $a->_entity->get('name')->getString() <=> $b->_entity->get('name')->getString());
    }
  }

  /**
   * Implements hook_views_data_alter().
   *
   * @param array<string, mixed> $data
   *   The views data.
   */
  #[Hook('views_data_alter')]
  public function viewsDataAlter(array &$data): void {
    $data['tpr_unit']['emphasis_filter'] = [
      'title' => $this->t('Emphasis filter'),
      'filter' => [
        'title' => $this->t('Emphasis filter'),
        'help' => 'Filters units by emphasis.',
        'field' => 'nid',
        'id' => 'emphasis_filter',
      ],
    ];

    $data['tpr_unit']['educational_mission_filter'] = [
      'title' => $this->t('Educational mission'),
      'filter' => [
        'title' => $this->t('Educational mission'),
        'help' => 'Filters units by educational mission.',
        'field' => 'nid',
        'id' => 'educational_mission_filter',
      ],
    ];

    $data['tpr_unit']['study_programme_type_filter'] = [
      'title' => $this->t('Study programme type'),
      'filter' => [
        'title' => $this->t('Study programme type'),
        'help' => 'Filters units by study programme type.',
        'field' => 'nid',
        'id' => 'study_programme_type_filter',
      ],
    ];

    $data['tpr_unit_field_data']['high_school_language'] = [
      'title' => $this->t('High school language'),
      'filter' => [
        'title' => $this->t('High school language'),
        'help' => 'Filters high school units by language of instruction.',
        'field' => 'id',
        'id' => 'high_school_language',
      ],
    ];
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   *
   * @param array<string, mixed> $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  #[Hook('form_views_exposed_form_alter')]
  public function viewsExposedFormAlter(array &$form, FormStateInterface $form_state): void {

    // Handle only Unit search view form at this point.
    if ($form['#id'] !== 'views-exposed-form-high-school-search-block') {
      return;
    }

    // Get view from form state.
    $view = $form_state->getStorage()['view'];
    $current_language = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

    // Apply the cached meta fields values to form values.
    $cached = $this->cache->get(
      $view->id() .
      $view->current_display .
      $current_language .
      $view->args[0]
    );

    if ($cached) {
      $meta_fields = $cached->data;
      if (!empty($meta_fields['field_hs_search_meta_button'])) {
        $form['actions']['submit']['#value'] = $meta_fields['field_hs_search_meta_button'];
      }
    }
  }
}
