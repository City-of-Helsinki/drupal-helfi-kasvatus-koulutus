<?php

declare(strict_types = 1);

namespace Drupal\helfi_group\EventSubscriber;

use Drupal\Core\Menu\MenuLinkTreeManipulatorsAlterEvent;
use Drupal\Core\Routing\AdminContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Filters out the untranslated menu links.
 */
final class GroupMenuFilterByLanguage implements EventSubscriberInterface {

  /**
   * Menu to filter.
   *
   * @var string
   */
  protected string $menuName = 'group_menu_link_content';

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Routing\AdminContext $adminContext
   *   The admin context service.
   */
  public function __construct(private AdminContext $adminContext) {
  }

  /**
   * Responds to MenuLinkTreeEvents::ALTER_MANIPULATORS event.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeManipulatorsAlterEvent $event
   *   The event to subscribe to.
   */
  public function filter(MenuLinkTreeManipulatorsAlterEvent $event) : void {
    if ($this->adminContext->isAdminRoute()) {
      return;
    }

    $manipulators = &$event->getManipulators();

    $menuName = NULL;
    foreach ($event->getTree() as $item) {
      if (!$item->link) {
        continue;
      }
      $menuName = $item->link->getMenuName();
      break;
    }

    if (!$menuName || !str_contains($menuName, $this->menuName)) {
      return;
    }

    $manipulators[] = [
      'callable' => 'menu_block_current_language_tree_manipulator::filterLanguages',
      'args' => [['menu_link_content']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      'menu.link_tree.alter_manipulators' => [
        ['filter'],
      ],
    ];
  }

}
