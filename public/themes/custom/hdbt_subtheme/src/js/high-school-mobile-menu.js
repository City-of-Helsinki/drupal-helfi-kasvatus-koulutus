(function ($, Drupal) {
  'use strict';
  // Find the menu item in the mobile menu that should work as the parent item
  // for this group menu. This is used in the code to target where to append the
  // group menu. We need to make sure this class is defined somewhere in the
  // menu. Also we need to make sure that the navigation on the sidebar is group
  // menu navigation.
  if (!$('.cssmenu-menu .high-school-parent').length || !$('.sidebar-navigation--group-menu').length) {
    return;
  }

  // Save the menu link to variable now that we know it exists.
  const menuLinkParent = document.querySelector('.cssmenu-menu .high-school-parent');

  // Find the parent item of the menu item that should work as parent item for
  // the group menu item. This is used later in the code to append the required
  // DOM for the menu to work correctly.
  const parentListItem = menuLinkParent.parentElement;

  // Make sure we have the sidebar navigation that needs to be appended inside
  // the mobile menu by checking if the group menus "title" is available in the
  // dom. If not, don't run the code any further.
  if (!document.querySelector('.sidebar-navigation__title > a')) {
    return;
  }

  // Add classes to indicate active-trail to the list items and links.
  parentListItem.classList.add('cssnav__item--in-path');
  $(parentListItem).parentsUntil('.cssmenu-menu', 'li').addClass('cssnav__item--in-path');
  $('.cssnav__item--in-path > a').addClass('cssnav__link--in-path');
  $('.cssnav__item--in-path > details').attr('open', '');

  // Find the group menus "title" that is actually the first level of the sub
  // navigation.
  const menuTitleLink = document.querySelector('.sidebar-navigation__title > a');

  // Clone the first level item to be used for mobile navigation.
  const cloneMenuTitleLink = menuTitleLink.cloneNode(true);

  // Add required class for the first level link.
  cloneMenuTitleLink.className = 'cssnav__link cssnav__link--in-path';

  // If there isn't any menu in the sidebar navigation we don't need to clone
  // anything.
  if (!document.querySelector('.sidebar-navigation > .menu')) {
    return;
  }

  // Find the rest of the sidebar navigation tree.
  const sidebarNavigation = document.querySelector('.sidebar-navigation > .menu');

  // Clone the sidebar navigation tree.
  const cloneSidebarNavigation = sidebarNavigation.cloneNode(true);

  // Clean up the cloned sidebar navigation. Get the ul-element and
  // remove ul-element classes and add classes 'cssnav cssnav__subnav'
  cloneSidebarNavigation.className = 'cssnav cssnav__subnav';

  // Remove all classes from the li-elements and add classes
  // 'cssnav__item cssnav__item--level-4" and remove the wrapper span.
  // Remove also the a-element classes and add classes 'cssnav__link'.
  const modify_list_item = (element) => {
    element.className = 'cssnav__item cssnav__item--level-5';
    const link = element.querySelector('span > a');
    element.querySelector('span').replaceWith(link);
    link.classList.add('cssnav__link');

    // Check if one of links is the active one and change the class to correct
    // one.
    if ($(link).hasClass('menu__link--active-trail')) {
      $(link).addClass('cssnav__link--in-path');
      $(link).removeClass('menu__link--active-trail');
    }
  };

  // After the ul-element is cleaned up loop through all li-elements. Using the
  // modify_list_item above for that.
  cloneSidebarNavigation.querySelectorAll('.menu__item').forEach(element => modify_list_item(element));

  // Create DOM-elements for the summary block inside navigation elements that
  // have children.
  const summary = document.createElement('summary');
  const spanTextMirror = document.createElement('span');
  const spanToggle = document.createElement('span');
  const spanVisuallyHidden = document.createElement('span');

  // Add correct classes for the span elements inside summary.
  spanTextMirror.className = 'cssnav__text-mirror';
  spanToggle.className = 'cssnav__toggle';
  spanVisuallyHidden.className = 'visually-hidden';

  // Add text to work as a placeholder for further replacement.
  spanTextMirror.innerHTML = 'placeholder';
  spanVisuallyHidden.innerHTML = Drupal.t('Toggle submenu:') + ' placeholder';

  // Insert the visually hidden span element inside the toggle span.
  spanToggle.append(spanVisuallyHidden);

  // Insert all spans inside summary element.
  summary.append(spanTextMirror, spanToggle);

  // Create details function that returns details dom-element based on variables
  // that are given to it.
  const create_details = (summary, content, linkName) => {
    let newSummary = summary.cloneNode(true);
    const details = document.createElement('details');
    const summaryTitle = linkName.textContent;
    // Replace all instances of placeholder with the summary title.
    newSummary.innerHTML = newSummary.innerHTML.replace(/placeholder/g, summaryTitle);
    details.append(newSummary);
    details.append(content);
    $(details).attr('open', '');
    return details;
  };

  // Create the list item element where the group menu is appended.
  const li = document.createElement('li');
  // We add the 'cssnav__item--has-children' class always since there
  // shouldn't be a case where only the high schools name alone is appended
  // to the menu.
  li.className = 'cssnav__item cssnav__item--level-4 cssnav__item--has-children';
  li.append(cloneMenuTitleLink);
  // Add the sub-items of the group menus first level to the details under the
  // list item.
  li.append(create_details(summary, cloneSidebarNavigation, cloneMenuTitleLink));

  // Check if there is more than one element inside the element that contains
  // the parent for link for high school group menu. Add the group menu and
  // details according this information.
  if (parentListItem.childElementCount > 1) {
    // Because there is already items under the high school parent link we need
    // to append just our group menu that is inside a list item after the
    // existing items.
    const parentSubNav = parentListItem.querySelector('.cssnav__subnav');
    parentSubNav.append(li);
  } else {
    // We need to create list element to wrap the list item where the group menu
    // is because there is no list element to append the group menu because
    // there is no other items under the high school parent link.
    const ul = document.createElement('ul');
    ul.className = 'cssnav cssnav__subnav';
    ul.append(li);
    // The DOM-structure needs also the details element to function correctly.
    const parentDetails = create_details(summary, ul, parentListItem);
    // Append everything to the parent list item.
    parentListItem.append(parentDetails);
    // Because the parent list item doesn't have any children until we add them
    // we need to give it proper classes as well.
    parentListItem.classList.add('cssnav__item--has-children');
  }
})(jQuery, Drupal);
