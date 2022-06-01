<?php

/**
 * @file
 * Builds extra placeholder replacement tokens for groups.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\helfi_group\GroupUtility;

/**
 * Implements hook_token_info().
 */
function helfi_group_token_info() {
  $type = [
    'name' => t('Group news parent'),
    'description' => t('Tokens for group news parent.'),
  ];

  $groupNewsParent['path'] = [
    'name' => t("Group news parent path"),
    'description' => t("Path to the news parent page set with a group field. Only applicable if content belongs to group."),
  ];

  return [
    'types' => ['group-news-parent' => $type],
    'tokens' => ['group-news-parent' => $groupNewsParent],
  ];
}

/**
 * Implements hook_tokens().
 */
function helfi_group_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  if ($type != 'group-news-parent' || empty($data['node'])) {
    return [];
  }
  if (!$data['node']->id()) {
    return [];
  }

  $replacements = [];

  foreach ($tokens as $name => $original) {
    switch ($name) {
      case 'path':
        if (!$group = GroupUtility::getGroup($data['node'])) {
          break;
        }

        // Get news parent node from group.
        if (!$group->hasField('field_group_news_parent')) {
          break;
        }
        if (!$newsParent = $group?->get('field_group_news_parent')
          ?->first()
          ?->get('entity')
          ?->getTarget()
          ?->getValue()) {
          $replacements[$original] = '';
          break;
        }

        // Use news parent translation that matches the current language.
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        /** @var \Drupal\Core\Entity\EntityInterface $newsParent */
        if ($newsParent->hasTranslation($langcode)) {
          $newsParent = $newsParent->getTranslation($langcode);
        }
        else {
          $replacements[$original] = '';
          break;
        }

        // Set the token replacement using the news parent path, but filtering
        // out langcode and site prefix sections.
        $replacements[$original] = _helfi_group_filter_path(
          $newsParent->toUrl('canonical')->toString(),
          [
            $langcode,
            _helfi_group_get_site_prefix($langcode),
          ]
        );
        break;
    }
  }

  return $replacements;
}

/**
 * Removes certain sections from path.
 *
 * @param string $path
 *   The original path.
 * @param string[] $filters
 *   Array of strings that are removed from the path.
 *
 * @return string
 *   The path without filtered sections.
 */
function _helfi_group_filter_path(string $path, array $filters) : string {
  $pathParts = array_filter(explode('/', $path));

  foreach ($filters as $filter) {
    $foundKey = array_search($filter, $pathParts, TRUE);
    if ($foundKey !== FALSE) {
      unset($pathParts[$foundKey]);
    }
  }

  return implode('/', $pathParts);
}

/**
 * Get site prefix from configs.
 *
 * @param string $langcode
 *   Langcode.
 *
 * @return string|null
 *   Site prefix.
 */
function _helfi_group_get_site_prefix(string $langcode) : ?string {
  if ($prefixes = \Drupal::configFactory()->get('helfi_proxy.settings')->get('prefixes')) {
    return $prefixes[$langcode];
  }
  return NULL;
}
