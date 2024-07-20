document.addEventListener('DOMContentLoaded', function () {
  const widgetContainer = document.querySelector('.ast-filter-wrap');
  const checkboxes = document.querySelectorAll('.product-category-checkbox');

  if (!widgetContainer || !checkboxes) {
    return;
  }

  let buttonsContainer = createButtonContainer();

  let applyButton = document.getElementById('mls-apply-filters');
  if (!applyButton) {
    applyButton = createApplyButton(checkboxes);
  }

  let resetButton = document.getElementById('mls-reset-filters');
  if (!resetButton) {
    resetButton = createResetButton(checkboxes);
  }

  // Add buttons to the container
  buttonsContainer.appendChild(applyButton);
  buttonsContainer.appendChild(resetButton);

  // Insert the container after the widgetContainer
  widgetContainer.insertAdjacentElement('afterend', buttonsContainer);

  // Parse the URL query parameters
  const urlParams = new URLSearchParams(window.location.search);
  const productCategories = urlParams.get('product_categories');
  const categoriesArray = productCategories ? productCategories.split(',') : [];

  // Check the corresponding checkboxes based on URL parameters
  checkboxes.forEach((checkbox) => {
    if (categoriesArray.includes(checkbox.value)) {
      checkbox.checked = true;
    }
  });

  // Initially set the Apply button's disabled state based on checked checkboxes
  applyButton.disabled = !Array.from(checkboxes).some(
    (checkbox) => checkbox.checked
  );

  // Enable Apply button if any checkbox is checked
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', () => {
      const isAnyChecked = Array.from(checkboxes).some(
        (checkbox) => checkbox.checked
      );
      applyButton.disabled = !isAnyChecked;
    });
  });
});

function createButtonContainer() {
  const container = document.createElement('div');
  container.classList.add('mls-filter-buttons-container');
  return container;
}

function createApplyButton(checkboxes) {
  const applyButton = document.createElement('button');
  applyButton.id = 'mls-apply-filters';
  applyButton.classList.add('mls-filter-buttons', 'mls-apply-filters');
  applyButton.textContent = 'Apply';

  applyButton.addEventListener('click', () => {
    const checkedCategories = Array.from(checkboxes)
      .filter((checkbox) => checkbox.checked)
      .map((checkbox) => checkbox.value);
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('product_categories', checkedCategories.join(','));
    window.location.search = urlParams.toString();
  });

  return applyButton;
}

function createResetButton(checkboxes) {
  const resetButton = document.createElement('button');
  resetButton.id = 'mls-reset-filters';
  resetButton.classList.add('mls-filter-buttons', 'mls-reset-filters');
  resetButton.textContent = 'Reset';

  resetButton.addEventListener('click', () => {
    checkboxes.forEach((checkbox) => {
      checkbox.checked = false;
    });

    document.getElementById('mls-apply-filters').disabled = true;
    window.location.href = window.location.pathname;
  });

  return resetButton;
}
