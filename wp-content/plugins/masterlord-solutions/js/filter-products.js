console.log('Filter products script loaded!');
class AttributeFilter {
  filterName;
  attributeCheckboxes;
}

document.addEventListener('DOMContentLoaded', function () {
  const widgetContainer = document.querySelector('.ast-filter-wrap');
  const filterWrapper = document.querySelector('.wc-blocks-filter-wrapper');
  const checkboxes = document.querySelectorAll('.product-category-checkbox');

  if (!widgetContainer || !checkboxes) {
    return;
  }

  let buttonsContainer = createButtonContainer();
  let applyButton = document.getElementById('mls-apply-filters');
  let resetButton = document.getElementById('mls-reset-filters');

  // Parse the URL query parameters
  const urlParams = new URLSearchParams(window.location.search);
  const activeQueryExists = urlParams.toString().length > 1;
  // console.log('activeQueryExists', activeQueryExists);
  const productCategories = urlParams.get('product_categories');
  const categoriesArray = productCategories ? productCategories.split(',') : [];
  // console.log('Categories array:', categoriesArray);

  // Check the corresponding checkboxes based on URL parameters
  checkboxes.forEach((checkbox) => {
    if (categoriesArray.includes(checkbox.value)) {
      checkbox.checked = true;
    }
  });

  const widgetContainerObserver = new MutationObserver(
    (mutationsList, observer) => {
      setTimeout(() => {
        // console.log('Mutation observed:', mutationsList);
        const wcAttributeFilterActions = document.querySelectorAll(
          '.wc-block-attribute-filter__actions'
        );
        // console.log('Attribute filter actions:', wcAttributeFilterActions);
        if (wcAttributeFilterActions && wcAttributeFilterActions.length > 0) {
          wcAttributeFilterActions.forEach((filter) => {
            filter.setAttribute('style', 'display: none;');
          });
        }

        const attributeCheckboxes = document.querySelectorAll(
          '.wc-block-components-checkbox__input'
        );
        if (attributeCheckboxes.length > 0) {
          let attributeFilters = [];
          const headers = document.querySelectorAll('h3.wp-block-heading');
          // console.log('Filter by attribute headers:', headers);

          if (!headers || headers.length === 0) {
            console.error('No filter by attribute headers found!');
          }

          headers.forEach((header) => {
            const attributeFilter = new AttributeFilter();
            attributeFilter.filterName =
              'filter_' + header.textContent.toLowerCase();
            // console.log('Attribute filter name:', attributeFilter.filterName);
            // Step 1: Find the closest common ancestor container. Assuming the direct parent is the container in this case.
            const container = header.nextElementSibling;
            if (container) {
              // console.log('Container:', container);

              // Step 2: Query all inputs within this container
              const inputs = container
                ? container.querySelectorAll(
                    '.wc-block-components-checkbox__input'
                  )
                : [];

              if (inputs.length > 0) {
                // console.log('Inputs:', inputs);
                // Step 3: Add the inputs to the attributeFilter object
                attributeFilter.attributeCheckboxes = inputs;
                // Step 4: Add the attributeFilter object to the attributeFilters array
                attributeFilters.push(attributeFilter);
              } else {
                console.error(
                  'No checkbox inputs found for this filter by attribute label:',
                  header.textContent.toLowerCase()
                );
              }
            } else {
              console.error(
                'No cehckbox container found for this filter by attribute label:',
                header.textContent.toLowerCase()
              );
            }
          });
          // console.log('Attribute filters:', attributeFilters);
          if (!applyButton) {
            applyButton = createApplyButton(checkboxes, attributeFilters);
          }
          if (!resetButton) {
            resetButton = createResetButton(checkboxes, attributeFilters);
          }

          // Initially set the Apply button's disabled state based on checked checkboxes
          applyButton.hidden =
            (!Array.from(checkboxes).some((checkbox) => checkbox.checked) &&
              !Array.from(attributeCheckboxes).some(
                (checkbox) => checkbox.checked
              )) ||
            !activeQueryExists;

          // Initially set the Reset button's disabled state based on checked checkboxes
          resetButton.hidden =
            (!Array.from(checkboxes).some((checkbox) => checkbox.checked) &&
              !Array.from(attributeCheckboxes).some(
                (checkbox) => checkbox.checked
              )) ||
            !activeQueryExists;

          // Enable Apply button if any checkbox is checked
          attributeCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('click', (event) => {
              // Prevent the event from bubbling up to the parent container, so it won't immidiately reload the page
              // As the default filter by attributes widget does
              event.stopPropagation();

              updateApplyButtonState(
                applyButton,
                checkboxes,
                attributeCheckboxes,
                activeQueryExists
              );
              updateResetButtonState(
                resetButton,
                checkboxes,
                attributeCheckboxes,
                activeQueryExists
              );
            });
          });

          // Enable Apply button if any checkbox is checked
          checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('click', () => {
              // console.log('Category checkbox clicked:', checkbox);
              updateApplyButtonState(
                applyButton,
                checkboxes,
                attributeCheckboxes
              );
              updateResetButtonState(
                resetButton,
                checkboxes,
                attributeCheckboxes
              );
            });
          });

          // Add buttons to the container
          buttonsContainer.appendChild(applyButton);
          buttonsContainer.appendChild(resetButton);

          // Insert the container after the widgetContainer
          widgetContainer.insertAdjacentElement('afterend', buttonsContainer);

          observer.disconnect();
        }
      }, 500);
    }
  );

  console.log('Filter wrapper:', filterWrapper);
  // Check if admin added filter by attributes widgets
  // Then we need to wait for the attribute checkboxes to be rendered so we use the observer class
  // Because these are loaded after DOMContentLoaded event is fired
  if (filterWrapper) {
    widgetContainerObserver.observe(filterWrapper, {
      childList: true,
      subtree: true,
    });
  } else {
    if (!applyButton) {
      applyButton = createApplyButton(checkboxes);
    }

    if (!resetButton) {
      resetButton = createResetButton(checkboxes);
    }

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
    // Add buttons to the container
    buttonsContainer.appendChild(applyButton);
    buttonsContainer.appendChild(resetButton);

    // Insert the container after the widgetContainer
    widgetContainer.insertAdjacentElement('afterend', buttonsContainer);
  }
});

function createButtonContainer() {
  const container = document.createElement('div');
  container.classList.add('mls-filter-buttons-container');
  return container;
}

function createApplyButton(checkboxes, attributeFilters) {
  const applyButton = document.createElement('button');
  applyButton.id = 'mls-apply-filters';
  applyButton.classList.add('mls-filter-buttons', 'mls-apply-filters');
  applyButton.textContent = 'Apply';

  if (attributeFilters) {
    applyButton.addEventListener('click', () => {
      const selectedCategories = Array.from(checkboxes)
        .filter((checkbox) => checkbox.checked)
        .map((checkbox) => checkbox.value);

      const urlParams = new URLSearchParams();

      if (selectedCategories.length > 0) {
        urlParams.set('product_categories', selectedCategories.join(','));
      }

      attributeFilters.forEach((attributeFilter) => {
        const selectedAttributes = Array.from(
          attributeFilter.attributeCheckboxes
        )
          .filter((checkbox) => checkbox.checked)
          .map((checkbox) => checkbox.id);

        if (selectedAttributes.length > 0) {
          const attributeName = attributeFilter.filterName.split('_')[1];
          urlParams.set('query_type_' + attributeName, 'or');

          urlParams.set(
            attributeFilter.filterName,
            selectedAttributes.join(',')
          );
        }
      });

      window.location.search = urlParams.toString();
    });
  } else {
    applyButton.addEventListener('click', () => {
      const checkedCategories = Array.from(checkboxes)
        .filter((checkbox) => checkbox.checked)
        .map((checkbox) => checkbox.value);
      if (checkedCategories.length > 0) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('product_categories', checkedCategories.join(','));
        window.location.search = urlParams.toString();
      } else {
        window.location.href = window.location.pathname;
      }
    });
  }

  return applyButton;
}

// Function to update the state of the Apply button
function updateApplyButtonState(applyButton, checkboxes, attributeCheckboxes) {
  const isAnyCategoryChecked = Array.from(checkboxes).some(
    (checkbox) => checkbox.checked
  );
  const isAnyAttributeChecked = Array.from(attributeCheckboxes).some(
    (checkbox) => checkbox.checked
  );

  applyButton.hidden =
    !(isAnyCategoryChecked || isAnyAttributeChecked) && !activeQueryExists;
}

// Function to update the state of the Apply button
function updateResetButtonState(
  resetButton,
  checkboxes,
  attributeCheckboxes,
  activeQueryExists
) {
  const isAnyCategoryChecked = Array.from(checkboxes).some(
    (checkbox) => checkbox.checked
  );
  const isAnyAttributeChecked = Array.from(attributeCheckboxes).some(
    (checkbox) => checkbox.checked
  );

  resetButton.hidden =
    !(isAnyCategoryChecked || isAnyAttributeChecked) && !activeQueryExists;
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
