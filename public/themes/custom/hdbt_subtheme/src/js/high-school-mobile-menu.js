(function ($, Drupal) {
  'use strict';

  // TODO: Write WHY this complex structure is created (menu--mobile.html.twig)

  $(document).ready(function () {
    if(!checkRequiredElements()) {
      return;
    }

    // Save the menu link to variable now that we know it exists.
    const mobileGroupMenuContainer = $('.cssmenu-menu .high-school-parent').parent();

    // Add classes to indicate active-trail to the list items and links.
    mobileGroupMenuContainer.addClass('cssnav__item--in-path');
    $(mobileGroupMenuContainer).parentsUntil('.cssmenu-menu', 'li').addClass('cssnav__item--in-path');
    $('.cssnav__item--in-path > a').addClass('cssnav__link--in-path');
    $('.cssnav__item--in-path > details').attr('open', '');

    // Clone the first level item to be used for mobile navigation.
    const menuTitleLink = $('.sidebar-navigation__title > a').clone();

    // Add required class for the first level link.
    menuTitleLink.attr('class', 'cssnav__link cssnav__link--in-path');

    // Clone the sidebar navigation tree.
    const sidebarNavigationElement = $('.sidebar-navigation > .menu').clone();

    // Clean up the cloned sidebar navigation. Get the ul-element and
    // remove ul-element classes and add classes 'cssnav cssnav__subnav'
    sidebarNavigationElement.attr('class', 'cssnav cssnav__subnav');

    // After the ul-element is cleaned up loop through all li-elements. Using the
    // modify_list_item above for that.
    $(sidebarNavigationElement).find('.menu__item').each(function() {
      $(this).attr('class', 'cssnav__item cssnav__item--level-5');
      const link = $(this).find('span > a');
      $(this).find('span')[0].remove();
      link.appendTo($(this));
      link.addClass('cssnav__link');

      // Check if one of links is the active one and change the class to correct
      // one.
      if ($(link).hasClass('menu__link--active-trail')) {
        $(link).addClass('cssnav__link--in-path');
        $(link).removeClass('menu__link--active-trail');
      }
    });

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

    // Create the list item element where the group menu is appended.
    const li = document.createElement('li');
    // We add the 'cssnav__item--has-children' class always since there
    // shouldn't be a case where only the high schools name alone is appended
    // to the menu.
    li.className = 'cssnav__item cssnav__item--level-4 cssnav__item--has-children';
    menuTitleLink.appendTo(li);
    // Add the sub-items of the group menus first level to the details under the
    // list item.
    li.append(createDetailsElement(summary, sidebarNavigationElement, menuTitleLink));

    // Check if there is more than one element inside the element that contains
    // the parent for link for high school group menu. Add the group menu and
    // details according this information.
    if (mobileGroupMenuContainer.childElementCount > 1) {
      // Because there is already items under the high school parent link we need
      // to append just our group menu that is inside a list item after the
      // existing items.
      const parentSubNav = mobileGroupMenuContainer.querySelector('.cssnav__subnav');
      parentSubNav.append(li);
    } else {
      // We need to create list element to wrap the list item where the group menu
      // is because there is no list element to append the group menu because
      // there is no other items under the high school parent link.
      const ul = document.createElement('ul');
      ul.className = 'cssnav cssnav__subnav';
      ul.append(li);
      // The DOM-structure needs also the details element to function correctly.
      const parentDetails = createDetailsElement(summary, ul, mobileGroupMenuContainer);
      // Append everything to the parent list item.
      mobileGroupMenuContainer.append(parentDetails);
      // Because the parent list item doesn't have any children until we add them
      // we need to give it proper classes as well.
      mobileGroupMenuContainer.addClass('cssnav__item--has-children');
    }
  });

  const checkRequiredElements = () => {
    // TODO
    if (!$('.cssmenu-menu .high-school-parent').length || !$('.sidebar-navigation--group-menu').length) {
      return false;
    }
    // TODO
    if (!$('.sidebar-navigation__title > a')) {
      return false;
    }
    // TODO
    if (!$('.sidebar-navigation > .menu')) {
      return false;
    }

    return true;
  };

  // Create details function that returns details dom-element based on variables
  // that are given to it.
  const createDetailsElement = (summary, content, linkName) => {
    let newSummary = summary.cloneNode(true);
    const details = document.createElement('details');
    const summaryTitle = linkName.textContent;
    // Replace all instances of placeholder with the summary title.
    newSummary.innerHTML = newSummary.innerHTML.replace(/placeholder/g, summaryTitle);
    $(newSummary).appendTo(details);
    $(content).appendTo(details);
    $(details).attr('open', '');
    return details;
  };

})(jQuery, Drupal);
