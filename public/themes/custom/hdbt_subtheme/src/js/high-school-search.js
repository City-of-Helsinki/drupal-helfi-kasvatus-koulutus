((Drupal) => {
  Drupal.behaviors.HighSchoolSearch = {
    attach: function attach(context) {
      // Set attributes based on whether the select should be disabled or not.
      const toggleSelectActivity = (select, disabled) => {
        const helpText = select.nextElementSibling;
        if (disabled) {
          helpText.textContent = Drupal.t(
            'Filter on the form has been selected and some of the other ' +
              'filters might be dimmed because of your selection. To use the ' +
              'filters that were dimmed, select the option "All" on the selected filter.',
          );
          select.parentElement?.classList.add('hdbt--select-wrapper--disabled');
          select.disabled = true;
          select.dataset.ariaDisabled = 'true';
        } else {
          helpText.textContent = '';
          select.parentElement?.classList.remove('hdbt--select-wrapper--disabled');
          select.disabled = false;
          select.dataset.ariaDisabled = 'false';
        }
      };

      // Disable all other selects except the one that has some OTHER option selected
      // than 'All' and enable them all if they all have 'All' option selected.
      const disableOtherSelects = (selected, selectElements) => {
        if (selected.value !== 'All') {
          /** biome-ignore lint/suspicious/useIterableCallbackReturn: @todo UHF-12501 */
          selectElements.forEach((select) => toggleSelectActivity(select, select !== selected));
        } else {
          selectElements.forEach((select) => {
            if (select !== selected) {
              toggleSelectActivity(select, false);
            }
          });
        }
      };

      // Find all select elements inside high school search.
      const selectElements = context.querySelectorAll(
        '.unit-search--high-school .js-form-item-emphasis .form-select, ' +
          '.unit-search--high-school .js-form-item-mission .form-select',
      );

      /** biome-ignore lint/suspicious/useIterableCallbackReturn: @todo UHF-12501 */
      selectElements.forEach((select) => disableOtherSelects(select, selectElements));

      selectElements.forEach((select) => {
        select.addEventListener('change', () => disableOtherSelects(select, selectElements));
      });
    },
  };
})(Drupal);
