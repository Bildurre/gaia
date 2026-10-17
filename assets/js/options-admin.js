// Ajustes de Gaia pages: applies the values of the form (or the saved ones,
// for options on other pages) to the thumbnails and samples, through the
// same map as the site (inc/option-styles.php, in window.gaiaOptionStyles).
const VALUE_CHECK_INTERVAL = 500;

function getRelativeLuminance(hexColor) {
  let hexDigits = hexColor.replace('#', '');

  if (hexDigits.length === 3) {
    hexDigits = [...hexDigits].map((hexDigit) => hexDigit + hexDigit).join('');
  }

  if (!/^[0-9a-f]{6}$/i.test(hexDigits)) {
    return 1;
  }

  const [red, green, blue] = [0, 2, 4].map((channelStart) => {
    const channel = parseInt(hexDigits.slice(channelStart, channelStart + 2), 16) / 255;

    return channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4;
  });

  return 0.2126 * red + 0.7152 * green + 0.0722 * blue;
}

function getContrastRatio(firstLuminance, secondLuminance) {
  return (Math.max(firstLuminance, secondLuminance) + 0.05) / (Math.min(firstLuminance, secondLuminance) + 0.05);
}

function initOptionsAdmin() {
  const optionStyles = window.gaiaOptionStyles;
  const optionsForm = document.querySelector('form#post');

  if (!optionStyles || !optionsForm) {
    return;
  }

  const { map: styleMap, savedValues, contrastTextColors } = optionStyles;

  const isDarkColor = (hexColor) => {
    const colorLuminance = getRelativeLuminance(hexColor);

    return getContrastRatio(colorLuminance, getRelativeLuminance(contrastTextColors.light))
      >= getContrastRatio(colorLuminance, getRelativeLuminance(contrastTextColors.dark));
  };

  // Top level option fields of this page, by option name
  const getOptionField = (optionName) => optionsForm.querySelector(`.acf-field[data-name="${optionName}"]:not(.acf-field .acf-field)`);

  const readFieldValue = (optionField) => {
    if (optionField.classList.contains('acf-field-radio')) {
      return optionField.querySelector('input[type="radio"]:checked')?.value ?? '';
    }

    if (optionField.classList.contains('acf-field-true-false')) {
      return optionField.querySelector('input[type="checkbox"]')?.checked ? '1' : '0';
    }

    if (optionField.classList.contains('acf-field-range')) {
      return optionField.querySelector('input[type="number"], input[type="range"]')?.value ?? '';
    }

    return optionField.querySelector('input[type="text"], select, textarea')?.value ?? '';
  };

  const getOptionValue = (optionName) => {
    const optionField = getOptionField(optionName);
    const rawValue = optionField ? readFieldValue(optionField) : savedValues[optionName];
    const mapEntry = styleMap[optionName];

    if (!mapEntry) {
      return rawValue ?? '';
    }

    if (mapEntry.type === 'color') {
      return /^#([0-9a-f]{3}){1,2}$/i.test(rawValue ?? '') ? rawValue : '';
    }

    if (mapEntry.type === 'number') {
      return rawValue === '' || rawValue === undefined ? String(mapEntry.default) : String(rawValue);
    }

    const validValues = Object.keys(mapEntry.values ?? {});

    if (rawValue === '' || rawValue === undefined || rawValue === null || (validValues.length && !validValues.includes(String(rawValue)))) {
      return String(mapEntry.default);
    }

    return String(rawValue);
  };

  const getFirstColor = (optionNames, fallbackColor = '') => {
    for (const optionName of optionNames) {
      const optionColor = getOptionValue(optionName);

      if (optionColor) {
        return optionColor;
      }
    }

    return fallbackColor;
  };

  // Custom properties of every mapped option (as the site prints them)
  const getCustomProperties = () => {
    const customProperties = {};

    Object.entries(styleMap).forEach(([optionName, mapEntry]) => {
      const optionValue = getOptionValue(optionName);

      if (mapEntry.type === 'color') {
        let colorValue = optionValue;

        if (!colorValue && mapEntry.contrast_of) {
          const sourceColor = getFirstColor(mapEntry.contrast_of, mapEntry.fallback ?? '');
          colorValue = sourceColor ? (isDarkColor(sourceColor) ? contrastTextColors.light : contrastTextColors.dark) : '';
        }

        if (colorValue) {
          customProperties[mapEntry.property] = colorValue;
        }
      } else if (mapEntry.type === 'number') {
        customProperties[mapEntry.property] = `${optionValue}${mapEntry.unit}`;
      } else if (mapEntry.type === 'choice') {
        Object.assign(customProperties, mapEntry.values?.[optionValue] ?? {});
      }
    });

    return customProperties;
  };

  let appliedPropertyNames = [];

  const applyCustomProperties = () => {
    const rootStyle = document.documentElement.style;
    const customProperties = getCustomProperties();

    appliedPropertyNames.forEach((propertyName) => {
      if (!(propertyName in customProperties)) {
        rootStyle.removeProperty(propertyName);
      }
    });

    Object.entries(customProperties).forEach(([propertyName, propertyValue]) => {
      rootStyle.setProperty(propertyName, propertyValue);
    });

    appliedPropertyNames = Object.keys(customProperties);
  };

  // Samples live in shadow roots
  const sampleRoots = [...document.querySelectorAll('.gaia-sample')].map((sampleHost) => sampleHost.shadowRoot).filter(Boolean);
  const querySamples = (selector) => sampleRoots.flatMap((sampleRoot) => [...sampleRoot.querySelectorAll(selector)]);

  const getBackgroundSchemeClass = (mapEntry, backgroundMode) => {
    let backgroundColor = '';

    if (backgroundMode === 'primary') {
      backgroundColor = getFirstColor(['colors_primary'], optionStyles.defaultPrimaryColor);
    } else if (backgroundMode === 'custom') {
      backgroundColor = getOptionValue(mapEntry.color_option);
    }

    if (backgroundMode === 'dark' || (backgroundColor && isDarkColor(backgroundColor))) {
      return 'scheme-dark';
    }

    return backgroundColor || backgroundMode === 'light' ? 'scheme-light' : '';
  };

  const applySampleClasses = () => {
    Object.entries(styleMap).forEach(([optionName, mapEntry]) => {
      const classSettings = mapEntry.class;

      if (!classSettings) {
        return;
      }

      const optionValue = getOptionValue(optionName);

      querySamples(classSettings.selector).forEach((sampleElement) => {
        if (classSettings.when_set) {
          sampleElement.classList.toggle(classSettings.when_set, optionValue !== '');
          return;
        }

        const replacedClasses = classSettings.choices
          ? classSettings.choices.map((choice) => classSettings.prefix + choice)
          : [...sampleElement.classList].filter((className) => className.startsWith(classSettings.prefix));
        sampleElement.classList.remove(...replacedClasses);
        sampleElement.classList.add(classSettings.prefix + optionValue);

        if (mapEntry.type === 'background') {
          const schemeClass = getBackgroundSchemeClass(mapEntry, optionValue);
          sampleElement.classList.remove('scheme-dark', 'scheme-light');

          if (schemeClass) {
            sampleElement.classList.add(schemeClass);
          }
        }
      });
    });
  };

  // Markup variants (logos, footer layouts, icon collections)
  const applySampleVariants = () => {
    querySamples('[data-sample-variants]').forEach((variantsElement) => {
      const optionField = getOptionField(variantsElement.dataset.sampleVariants);

      if (!optionField) {
        return;
      }

      const selectedValue = readFieldValue(optionField);
      variantsElement.querySelectorAll(':scope > [data-variant-value]').forEach((variantElement) => {
        variantElement.hidden = variantElement.dataset.variantValue !== selectedValue;
      });
    });
  };

  // Texts bound to text fields (by field key), e.g. the announcement
  const applySampleTexts = () => {
    querySamples('[data-sample-text]').forEach((textElement) => {
      const textInput = optionsForm.querySelector(`.acf-field[data-key="${textElement.dataset.sampleText}"] input`);

      if (!textInput) {
        return;
      }

      textElement.dataset.originalText ??= textElement.textContent;
      textElement.textContent = textInput.value.trim() || textElement.dataset.originalText;
    });
  };

  // Samples may load before main.css applies: keep links inside them inert
  sampleRoots.forEach((sampleRoot) => {
    sampleRoot.addEventListener('click', (clickEvent) => {
      if (clickEvent.target.closest('a')) {
        clickEvent.preventDefault();
      }
    });
  });

  let lastFormSignature = '';

  const getFormSignature = () => new URLSearchParams(
    [...new FormData(optionsForm)].filter(([fieldName, fieldValue]) => fieldName.startsWith('acf[') && typeof fieldValue === 'string')
  ).toString();

  const applyAll = () => {
    lastFormSignature = getFormSignature();
    applyCustomProperties();
    applySampleClasses();
    applySampleVariants();
    applySampleTexts();
  };

  optionsForm.addEventListener('input', applyAll);
  optionsForm.addEventListener('change', applyAll);

  // Color pickers and some fields change their inputs without native events
  window.setInterval(() => {
    if (getFormSignature() !== lastFormSignature) {
      applyAll();
    }
  }, VALUE_CHECK_INTERVAL);

  applyAll();
}

initOptionsAdmin();
