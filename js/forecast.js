(function (Drupal, once) {
  Drupal.behaviors.nomeModuloTemperatureUnit = {
    attach(context) {
      once(
        'nome-modulo-temperature-unit',
        '.weather-forecast',
        context,
      ).forEach((forecast) => {
        const temperatureElement = forecast.querySelector(
          '.weather-forecast__temperature',
        );
        const toggle = forecast.querySelector(
          '.weather-forecast__unit-toggle',
        );
        const celsius = Number(forecast.dataset.temperatureCelsius);

        if (!temperatureElement || !toggle || Number.isNaN(celsius)) {
          return;
        }

        toggle.addEventListener('click', () => {
          const showingFahrenheit =
            toggle.getAttribute('aria-pressed') === 'true';

          if (showingFahrenheit) {
            temperatureElement.textContent = `${celsius} °C`;
            toggle.textContent = Drupal.t('Show °F');
            toggle.setAttribute('aria-pressed', 'false');
            return;
          }

          const fahrenheit = (celsius * 9) / 5 + 32;

          temperatureElement.textContent = `${fahrenheit.toFixed(1)} °F`;
          toggle.textContent = Drupal.t('Show °C');
          toggle.setAttribute('aria-pressed', 'true');
        });
      });
    },
  };
})(Drupal, once);
