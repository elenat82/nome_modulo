(function (Drupal, once) {
  "use strict";

  Drupal.behaviors.nomeModuloForecastToggle = {
    attach(context) {
      once(
        "nome-modulo-forecast-toggle",
        "[data-nome-modulo-forecast]",
        context,
      ).forEach((forecast) => {
        const button = forecast.querySelector("[data-forecast-toggle]");
        const details = forecast.querySelector("[data-forecast-details]");

        if (!button || !details) {
          return;
        }

        const showLabel =
          button.dataset.showLabel || Drupal.t("Show extended forecast");
        const hideLabel =
          button.dataset.hideLabel || Drupal.t("Hide extended forecast");

        button.addEventListener("click", () => {
          const isExpanded =
            button.getAttribute("aria-expanded") === "true";

          button.setAttribute(
            "aria-expanded",
            String(!isExpanded),
          );

          details.hidden = isExpanded;
          button.textContent = isExpanded
            ? showLabel
            : hideLabel;

          forecast.classList.toggle(
            "is-expanded",
            !isExpanded,
          );
        });
      });
    },
  };
})(Drupal, once);
