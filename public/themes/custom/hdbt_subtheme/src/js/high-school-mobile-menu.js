// Find the group menus "title" that is actually the first level of the sub
// navigation.
const menuTitleLink = document.querySelector('.sidebar-navigation__title > a');

// Clone the first level item to be used for mobile navigation.
const cloneMenuTitleLink = menuTitleLink.cloneNode(true);

// Add required class for the first level link.
cloneMenuTitleLink.className = 'cssnav__link';

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
  link.className = 'cssnav__link';
};

// After the ul-element is cleaned up loop through all li-elements. Using the
// modify_list_item above for that.
cloneSidebarNavigation.querySelectorAll('.menu__item').forEach(element => modify_list_item(element));

// Find the menu item in the mobile menu that should work as the parent item
// for this group menu. This is used in the code to target where to append the
// group menu.
const menuLinkParent = document.querySelector('.cssmenu-menu .high-school-parent');

// Find the parent item of the menu item that should work as parent item for the
// group menu item. This is used later in the code to append the required DOM
// for the menu to work correctly.
const parentListItem = menuLinkParent.parentElement;

// TODO: Write the summary based on the template menu--mobile.html.twig.
// Find example structure of the summary-element from the current menu.
const cloneSummary = document.querySelector('.cssnav summary').cloneNode(true);

const create_details = (cloneSummary, content, linkName) => {
  const newClone = cloneSummary.cloneNode(true);
  const details = document.createElement('details');
  //const summaryOriginalTitle = newClone.querySelector('.cssnav__text-mirror').textContent;
  const summaryTitle = linkName.textContent;

  // TODO: Make dynamic.
  newClone.innerHTML = `<span class="cssnav__text-mirror">${summaryTitle}</span>
  <span class="cssnav__toggle"><span class="visually-hidden">Kytke alivalikko: ${summaryTitle}</span></span>`;
  details.append(newClone);
  details.append(content);
  return details;
};


if (menuLinkParent.parentElement.childElementCount > 1) {
  // Remove the wrapping UL etc.
} else {
  const ul = document.createElement('ul');
  const li = document.createElement('li');
  ul.className = 'cssnav cssnav__subnav';
  // TODO: cssnav__item--has-children class needs to depend on the fact that if there is children or not for the high-school.
  li.className = 'cssnav__item cssnav__item--level-4 cssnav__item--has-children';
  li.append(cloneMenuTitleLink);
  ul.append(li);
  const parentDetails = create_details(cloneSummary, ul, parentListItem);
  parentListItem.append(parentDetails);

  // Add the sub-items of the group menus first level (if any) to the details under the li.
  li.append(create_details(cloneSummary, cloneSidebarNavigation, cloneMenuTitleLink))

  parentListItem.classList.add('cssnav__item--has-children');
}

/**
<ul class="">
  <li class="cssnav__item cssnav__item--level-4 cssnav__item--has-children">
    LINKKI
    <details>
      <summary>
        <span class="cssnav__text-mirror">LINKKI TEKSTI</span>
        <span class="cssnav__toggle">
          <span class="visually-hidden">Kytke alivalikko: LINKKI TEKSTI</span>
        </span>
      </summary>
      Sidebar Navigation
    </details>
  </li>
</ul>
**/
