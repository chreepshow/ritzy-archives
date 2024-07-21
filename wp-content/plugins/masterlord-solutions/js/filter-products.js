console.info('Filter products script loaded!');
class AttributeFilter {
  filterName;
  attributeCheckboxes;
}

function isMobile() {
  return window.innerWidth <= 800; // or any other width threshold you consider as mobile
}

document.addEventListener('DOMContentLoaded', function () {
  if (isMobile()) {
    filteringForMobile();
  } else {
    filteringForDesktop();
  }
});

function filteringForMobile() {
  const resultP = document.querySelector('.woocommerce-result-count');
  const productHeader = document.querySelector('.woocommerce-products-header');
  // Create the filter button element
  const filterButton = document.createElement('button');
  filterButton.id = 'open-filter-menu';
  filterButton.className = 'filter-button';
  filterButton.textContent = 'Filter Products';

  if (resultP) {
    resultP.insertAdjacentElement('afterend', filterButton);
  } else {
    productHeader.insertAdjacentElement('afterend', filterButton);
  }

  // Astra filter container selector, containing categories too
  let widgetContainer = document.querySelectorAll('.ast-filter-wrap');
  widgetContainer = Array.from(widgetContainer).filter((wc) =>
    wc.closest('.filter-menu-content')
  );
  // Attribute filter container selector
  let filterWrapper = document.querySelectorAll('.wc-blocks-filter-wrapper');
  filterWrapper = Array.from(filterWrapper).filter((fw) =>
    fw.closest('.filter-menu-content')
  );
  let checkboxes = document.querySelectorAll('.product-category-checkbox');
  // Filter out checkboxes that are descendants of '.filter-menu-content'
  checkboxes = Array.from(checkboxes).filter((checkbox) =>
    checkbox.closest('.filter-menu-content')
  );

  if (!widgetContainer || !checkboxes) {
    console.error('No .ast-filter-wrap elements found to observe.');
    return;
  }

  let buttonsContainer = createButtonContainer('mobile');
  let applyButton = document.getElementById('mls-apply-filters-mobile');
  let resetButton = document.getElementById('mls-reset-filters-mobile');

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
        let wcAttributeFilterActions = document.querySelectorAll(
          '.wc-block-attribute-filter__actions'
        );
        wcAttributeFilterActions = Array.from(wcAttributeFilterActions).filter(
          (filterActions) => filterActions.closest('.filter-menu-content')
        );
        // console.log('Attribute filter actions:', wcAttributeFilterActions);
        if (wcAttributeFilterActions && wcAttributeFilterActions.length > 0) {
          wcAttributeFilterActions.forEach((filter) => {
            filter.setAttribute('style', 'display: none;');
          });
        }

        let attributeCheckboxes = document.querySelectorAll(
          '.wc-block-components-checkbox__input'
        );
        // Filter out checkboxes that are descendants of '.filter-menu-content'
        attributeCheckboxes = Array.from(attributeCheckboxes).filter(
          (checkbox) => checkbox.closest('.filter-menu-content')
        );
        if (attributeCheckboxes.length > 0) {
          let attributeFilters = [];
          let headers = document.querySelectorAll('h3.wp-block-heading');
          headers = Array.from(headers).filter((header) =>
            header.closest('.filter-menu-content')
          );
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
            applyButton = createApplyButton(
              checkboxes,
              attributeFilters,
              'mobile'
            );
          }
          if (!resetButton) {
            resetButton = createResetButton(
              checkboxes,
              attributeFilters,
              'mobile'
            );
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
          widgetContainer[0].insertAdjacentElement(
            'afterend',
            buttonsContainer
          );

          observer.disconnect();
        }
      }, 100);
    }
  );

  // console.log('Filter wrapper:', filterWrapper);
  // Check if admin added filter by attributes widgets
  // Then we need to wait for the attribute checkboxes to be rendered so we use the observer class
  // Because these are loaded after DOMContentLoaded event is fired
  if (filterWrapper) {
    if (widgetContainer.length > 1) {
      console.error('More widget containers found than expected!');
    } else {
      widgetContainerObserver.observe(widgetContainer[0], {
        childList: true,
        subtree: true,
      });
    }
  } else {
    if (!applyButton) {
      applyButton = createApplyButton(checkboxes, null, 'mobile');
    }

    if (!resetButton) {
      resetButton = createResetButton(checkboxes, null, 'mobile');
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
    widgetContainer[0].insertAdjacentElement('afterend', buttonsContainer);
  }
}

function filteringForDesktop() {
  // Astra filter container selector, containing categories too
  let widgetContainer = document.querySelectorAll('.ast-filter-wrap');
  widgetContainer = Array.from(widgetContainer).filter(
    (wc) => !wc.closest('.filter-menu-content')
  );
  // Attribute filter container selector
  let filterWrapper = document.querySelectorAll('.wc-blocks-filter-wrapper');
  filterWrapper = Array.from(filterWrapper).filter(
    (fw) => !fw.closest('.filter-menu-content')
  );
  let checkboxes = document.querySelectorAll('.product-category-checkbox');
  // Filter out checkboxes that are descendants of '.filter-menu-content'
  checkboxes = Array.from(checkboxes).filter(
    (checkbox) => !checkbox.closest('.filter-menu-content')
  );

  if (!widgetContainer || !checkboxes) {
    return;
  }

  let buttonsContainer = createButtonContainer('desktop');
  let applyButton = document.getElementById('mls-apply-filters-desktop');
  let resetButton = document.getElementById('mls-reset-filters-desktop');

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
        let wcAttributeFilterActions = document.querySelectorAll(
          '.wc-block-attribute-filter__actions'
        );
        wcAttributeFilterActions = Array.from(wcAttributeFilterActions).filter(
          (filterActions) => !filterActions.closest('.filter-menu-content')
        );
        // console.log('Attribute filter actions:', wcAttributeFilterActions);
        if (wcAttributeFilterActions && wcAttributeFilterActions.length > 0) {
          wcAttributeFilterActions.forEach((filter) => {
            filter.setAttribute('style', 'display: none;');
          });
        }

        let attributeCheckboxes = document.querySelectorAll(
          '.wc-block-components-checkbox__input'
        );
        // Filter out checkboxes that are descendants of '.filter-menu-content'
        attributeCheckboxes = Array.from(attributeCheckboxes).filter(
          (checkbox) => !checkbox.closest('.filter-menu-content')
        );
        if (attributeCheckboxes.length > 0) {
          let attributeFilters = [];
          let headers = document.querySelectorAll('h3.wp-block-heading');
          headers = Array.from(headers).filter(
            (header) => !header.closest('.filter-menu-content')
          );
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
            applyButton = createApplyButton(
              checkboxes,
              attributeFilters,
              'desktop'
            );
          }
          if (!resetButton) {
            resetButton = createResetButton(
              checkboxes,
              attributeFilters,
              'desktop'
            );
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
          widgetContainer[0].insertAdjacentElement(
            'afterend',
            buttonsContainer
          );

          observer.disconnect();
        }
      }, 100);
    }
  );

  // console.log('Filter wrapper:', filterWrapper);
  // Check if admin added filter by attributes widgets
  // Then we need to wait for the attribute checkboxes to be rendered so we use the observer class
  // Because these are loaded after DOMContentLoaded event is fired
  if (filterWrapper) {
    if (widgetContainer.length > 1) {
      console.error('More widget containers found than expected!');
    } else {
      widgetContainerObserver.observe(widgetContainer[0], {
        childList: true,
        subtree: true,
      });
    }
  } else {
    if (!applyButton) {
      applyButton = createApplyButton(checkboxes, null, 'desktop');
    }

    if (!resetButton) {
      resetButton = createResetButton(checkboxes, null, 'desktop');
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
    widgetContainer[0].insertAdjacentElement('afterend', buttonsContainer);
  }
}

function createButtonContainer(desktopClass) {
  const container = document.createElement('div');
  container.classList.add('mls-filter-buttons-container-' + desktopClass);
  return container;
}

function createApplyButton(checkboxes, attributeFilters, desktopClass) {
  const applyButton = document.createElement('button');
  applyButton.id = 'mls-apply-filters-' + desktopClass;
  applyButton.classList.add('mls-apply-filters-' + desktopClass);
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
        const urlParams = new URLSearchParams();
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
function updateApplyButtonState(
  applyButton,
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

function createResetButton(checkboxes, attributeFilters, desktopClass) {
  const resetButton = document.createElement('button');
  resetButton.id = 'mls-reset-filters-' + desktopClass;
  resetButton.classList.add('mls-reset-filters-' + desktopClass);
  resetButton.textContent = 'Reset';

  resetButton.addEventListener('click', () => {
    checkboxes.forEach((checkbox) => {
      checkbox.checked = false;
    });

    document.getElementById(
      'mls-apply-filters-' + desktopClass
    ).disabled = true;
    window.location.href = window.location.pathname;
  });

  return resetButton;
}
