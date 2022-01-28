(function (Drupal) {
  Drupal.behaviors.HighSchoolSearch = {};

  Drupal.behaviors.HighSchoolSearch.attach = function (context, settings) {
    // Find all select elements inside high school search.
    const selectElements = context.querySelectorAll(
      '.unit-search--high-school .js-form-item-emphasis .form-select, ' +
      '.unit-search--high-school .js-form-item-mission .form-select'
    );

    selectElements.forEach(
      function(select) {
        disableOtherSelects(select, selectElements);
      }
    );

    for (let select of selectElements) {
      select.addEventListener('change', function (){
        disableOtherSelects(this, selectElements);
      });
    }
  };

  // Set attributes based on if the select should be disabled or not.
  function toggleSelectActivity(select, disabled) {
    const helpText = select.nextElementSibling;
    if (disabled) {
      helpText.textContent = Drupal.t(
        'Filter on the form has been selected and some of the other ' +
        'filters might be dimmed because of your selection. To use the ' +
        'filters that were dimmed select the option All on the selected ' +
        'filter.'
      );
      select.parentElement.classList.add('hdbt--select-wrapper--disabled');
      select.disabled = true;
      select.setAttribute("aria-disabled", "true");
    } else {
      helpText.textContent = '';
      select.parentElement.classList.remove(
        'hdbt--select-wrapper--disabled'
      );
      select.disabled = false;
      select.setAttribute("aria-disabled", "false");
    }
  }

  // Disable all other selects except the one that has some OTHER option selected
  // than 'All' and enable them all if they all have 'All' option selected.
  function disableOtherSelects(selected, selectElements) {
    if (selected.value !== 'All') {
      for (let select of selectElements) {
        toggleSelectActivity(select, select !== selected);
      }
    }
    else {
      for (let select of selectElements) {
        if (select !== selected) {
          toggleSelectActivity(select, false);
        }
      }
    }
  }

})(Drupal);
