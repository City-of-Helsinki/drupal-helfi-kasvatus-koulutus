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
use Drupal\views\ResultRow;
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

    foreach ($view->result as $row) {
      $entity = $this->getResultEntity($row, $current_language);
      $name = $entity->get('name')->getString();
      $name = trim(str_replace($removableStrings, '', $name));
      $entity->set('name', $name);
    }

    // Sort alphabetically based on the parsed name.
    uasort(
      $view->result,
      function (ResultRow $first, ResultRow $second) use ($current_language
      ): int {
        $first_entity = $this->getResultEntity($first, $current_language);
        $second_entity = $this->getResultEntity($second, $current_language);

        $first_name = $first_entity->get('name')->getString();
        $second_name = $second_entity->get('name')->getString();

        return $first_name <=> $second_name;
      }
    );
  }

  /**
   * Returns the result row entity in the given language.
   *
   * @param \Drupal\views\ResultRow $row
   *   The result row.
   * @param string $langcode
   *   The language code.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The entity translation when available.
   */
  private function getResultEntity(ResultRow $row, string $langcode): ContentEntityInterface {
    $entity = $row->_entity;
    assert($entity instanceof ContentEntityInterface);

    if ($entity->hasTranslation($langcode)) {
      return $entity->getTranslation($langcode);
    }

    return $entity;
  }

  /**
   * Implements hook_views_data_alter().
   *
   * @param array<string, mixed> $data
   *   The views data.
   */
  #[Hook('views_data_alter')]
  public function viewsDataAlter(array &$data): void {
    $filters = [
      [
        'tpr_unit',
        'emphasis_filter',
        'nid',
        $this->t('Emphasis filter'),
        'Filters units by emphasis.',
      ],
      [
        'tpr_unit',
        'educational_mission_filter',
        'nid',
        $this->t('Educational mission'),
        'Filters units by educational mission.',
      ],
      [
        'tpr_unit',
        'study_programme_type_filter',
        'nid',
        $this->t('Study programme type'),
        'Filters units by study programme type.',
      ],
      [
        'tpr_unit_field_data',
        'high_school_language',
        'id',
        $this->t('High school language'),
        'Filters high school units by language of instruction.',
      ],
    ];

    foreach ($filters as [$table, $id, $field, $title, $help]) {
      $data[$table][$id] = [
        'title' => $title,
        'filter' => [
          'title' => $title,
          'help' => $help,
          'field' => $field,
          'id' => $id,
        ],
      ];
    }
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
