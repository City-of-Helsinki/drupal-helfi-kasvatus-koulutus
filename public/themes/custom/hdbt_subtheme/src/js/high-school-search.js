// Find all select elements inside high school search.
const selectElements = document.querySelectorAll(
  '.unit-search--high-school .form-select'
);

// Disable all other selects except the one that has some OTHER option selected
// than 'All' and enable them all if they all have 'All' option selected.
function disableOtherSelects(selected) {
  if (selected.value !== 'All') {
    for (let select of selectElements) {
      if (select !== selected) {
        const helpText = selected.nextElementSibling;
        helpText.textContent = Drupal.t(
          'Filter on the form has been selected and some of the other ' +
          'filters might be dimmed because of your selection. To use the ' +
          'filters that were dimmed select the option All on the selected ' +
          'filter.'
        );
        select.parentElement.classList.add('hdbt--select-wrapper--disabled');
        select.disabled = true;
        select.setAttribute("aria-disabled", "true");
      }
    }
  }
  else {
    for (let select of selectElements) {
      const helpText = selected.nextElementSibling;
      helpText.textContent = '';
      select.parentElement.classList.remove(
        'hdbt--select-wrapper--disabled'
      );
      select.disabled = false;
      select.setAttribute("aria-disabled", "false");
    }
  }
}

// Listen to the change event on the high school search selects and trigger
// the disableOtherSelects function if change is detected.
document.addEventListener('DOMContentLoaded', function () {
  for (let select of selectElements) {
    select.addEventListener('change', function (){
      disableOtherSelects(this);
    });
  }
});
