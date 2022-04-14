const menuTitleLink = document.querySelector('.sidebar-navigation__title > a');
menuTitleLink.className = 'cssnav__link';
const sidebarNavigation = document.querySelector('.sidebar-navigation > .menu');

const menuLinkParent = document.querySelector('.cssmenu-menu .high-school-parent');
const parentListItem = menuLinkParent.parentElement;

// IF NO SUMMARY KILL CODE
const summaryClone = document.querySelector('.cssnav summary').cloneNode(true);

const clone = sidebarNavigation.cloneNode(true);

const modify_list_item = (element) => {
  element.className = 'cssnav__item cssnav__item--level-5';
  const link = element.querySelector('span > a');
  element.querySelector('span').replaceWith(link);
  link.className = 'cssnav__link';
};

const create_details = (summaryClone, content, linkName) => {
  const newClone = summaryClone.cloneNode(true);
  const details = document.createElement('details');
  //const summaryOriginalTitle = newClone.querySelector('.cssnav__text-mirror').textContent;
  const summaryTitle = linkName.textContent;

  // TODO: Make dynamic.
  newClone.innerHTML = `<span class="cssnav__text-mirror">${summaryTitle}</span>
              <span class="cssnav__toggle"><span class="visually-hidden">Kytke alivalikko: ${summaryTitle}</span></span>`//newClone.innerHTML.replace(`${summaryOriginalTitle}/g`,summaryTitle);
  console.log(newClone.innerHTML);
  details.append(newClone);
  details.append(content);
  return details;
};

// Get ul-element
// Remove ul-element classes and add classes 'cssnav cssnav__subnav'
sidebarNavigation.className = 'cssnav cssnav__subnav';
// Loop thorugh all li-elements
// Remove all classes from the li-elements and add classes 'cssnav__item cssnav__item--level-4"
// Remove wrapper span
// Remove a-element classes and add classes 'cssnav__link'
sidebarNavigation.querySelectorAll('.menu__item').forEach(element => modify_list_item(element));

if (menuLinkParent.parentElement.childElementCount > 1) {
  // Remove the wrapping UL etc.
} else {
  const ul = document.createElement('ul');
  const li = document.createElement('li');
  ul.className = 'cssnav cssnav__subnav';
  li.className = 'cssnav__item cssnav__item--level-4 cssnav__item--has-children';
  li.append(menuTitleLink);
  ul.append(li);
  const parentDetails = create_details(summaryClone, ul, menuTitleLink);
  parentListItem.append(parentDetails);

  //li.append(create_details(summaryClone, clone));



  parentListItem.classList.add('cssnav__item--has-children');
  //parentListItem.append(parentDetails);
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


//menuLinkParent.after(clone);

console.log(summaryClone);
