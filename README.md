# Nome Modulo

`nome_modulo` is a custom module for Drupal 11, developed as a learning and portfolio project.

The project is intended to put into practice the main concepts and APIs involved in modern Drupal module development, following Drupal coding standards and development best practices.

The project name was chosen so that everyone can customize it to their liking.

> **Status:** Work in progress.

## Goals

The main goals of this project are to:

- develop a complete Drupal 11 custom module incrementally;
- apply Drupal coding standards and conventions;
- use object-oriented PHP and Drupal's service container appropriately;
- keep code, configuration, documentation, and tests under version control;
- document architectural and implementation decisions as the project evolves;
- integrate automated code quality checks and documentation generation.

During development, the module will be used to explore and apply topics such as:

- routing, controllers, responses, menu links, and permissions;
- services and dependency injection;
- hooks and events;
- Plugin API and block plugins;
- Form API;
- Render API, Twig templates, translations, and asset libraries;
- Configuration API and State API;
- Entity API, Field API, and validation;
- Cron and Queue API;
- cacheability and performance;
- security;
- automated testing.
Database API and Batch API are intentionally not covered because the current module does not have a use case that would justify introducing them without adding artificial complexity.

## Current implementation

The module currently provides a weather forecast page available at:

`/weather`

The forecast page retrieves daily weather forecast data from the Open-Meteo API through a dedicated `ForecastClient` service.

The current implementation includes:

- a Drupal route handled by `ForecastController`;
- a dedicated `ForecastClient` service;
- constructor dependency injection through `ForecastClientInterface`;
- integration with the Open-Meteo API;
- normalization of external weather data into an internal forecast structure;
- a dedicated `LocationGeocoder` service integrating with the Open-Meteo Geocoding API;
- configurable forecast location through geocoded location search;
- multiple candidate selection for ambiguous location searches;
- automatic derivation of latitude, longitude, and timezone from the selected location;
- forecast retrieval using the geocoded coordinates;
- custom permissions controlling access to the forecast page and administrative settings;
- an administrative menu link for the settings page;
- a `Weather alert` content type provided through default configuration;
- configurable alert level, start date and end date fields;
- custom cross-field validation requiring the alert end date to be later than the start date;
- a custom event dispatched when a Weather alert is created;
- event subscriber integration for logging newly created Weather alerts;
- object-oriented hook implementations using Drupal's `#[Hook]` attribute;
- Render API and dedicated Twig templates for forecast output;
- a Drupal asset library providing component-specific CSS and JavaScript;
- a Drupal JavaScript behavior allowing the summary forecast to reveal the extended forecast without reloading the page;
- a `display` route parameter supporting summary and extended forecast views. In summary mode, the first forecast day is displayed initially and the remaining days can be revealed through a JavaScript toggle. In extended mode, all forecast days are displayed immediately;
- a configurable forecast block plugin supporting summary and extended display modes;
- forecast data caching with cache tags and automatic invalidation when module configuration changes;
- State API integration for tracking the last cron execution and the last successful forecast refresh;
- Cron integration for scheduling forecast refresh operations;
- Queue API integration for processing forecast refreshes outside the cron hook;
- automated unit, kernel, functional, and functional JavaScript tests.

The default forecast URL:

`/weather`

uses the summary display mode.

The following URLs are also available:

- /weather/summary
- /weather/extended

Only summary and extended are accepted as values for the display route parameter.

## Requirements

- Drupal 11.1 or later
- Composer
- Drush

The local development environment used for this project is based on DDEV.

## Installation

The module is currently under development.

Place the module in:

`web/modules/custom/nome_modulo`

Enable it with Drush:

```bash
ddev drush en nome_modulo -y
```

Rebuild Drupal caches:

```bash
ddev drush cr
```

The module settings are available at:

`/admin/config/services/nome-modulo`

The forecast page is available at:

`/weather`

## Development

The module is being developed incrementally, with small commits intended to keep the Git history readable and to make the evolution of the project easy to follow.

Current code quality and development tooling include:

- Drupal Coding Standards;
- PHP_CodeSniffer and Drupal Coder;
- automated unit, kernel, functional, and functional JavaScript tests;
- source-code documentation through PHPDoc/DocBlock comments;
- generated API documentation through phpDocumentor.

Additional development tooling will be introduced as the project evolves, including:
- static analysis;
- continuous integration with GitHub Actions.

## Code quality

The module follows Drupal coding standards and is checked with PHP_CodeSniffer and Drupal Coder.

From anywhere inside the DDEV project, run:

```bash
ddev exec --dir /var/www/html/web/modules/custom/nome_modulo /var/www/html/vendor/bin/phpcs
```

To automatically fix supported coding standard violations, run:

```bash
ddev exec --dir /var/www/html/web/modules/custom/nome_modulo /var/www/html/vendor/bin/phpcbf
```

After running PHP Code Beautifier and Fixer, review the changes with:

```bash
git diff
```

and run PHP_CodeSniffer again to verify that no violations remain.

The project's PHP_CodeSniffer rules are defined in `phpcs.xml.dist`.

## Static analysis

The module is analysed with PHPStan and `phpstan-drupal` at level 5.

PHPStan is installed at the Drupal project level rather than inside this module repository. In a Drupal project containing this module, install the required development dependencies with:

```bash
ddev composer require --dev phpstan/phpstan mglaman/phpstan-drupal
```

The repository provides its PHPStan configuration in `phpstan.neon.dist`.

From the Drupal project root, run the static analysis with:

```bash
ddev exec ./vendor/bin/phpstan analyse -c web/modules/custom/nome_modulo/phpstan.neon.dist
```

The configuration analyses both the module source code and its test suite.

## Testing

The project includes automated unit, kernel, functional and functional JavaScript tests.

Unit tests are implemented with Drupal's `UnitTestCase` and are used to verify isolated application logic without performing real HTTP requests.

The current unit test suite covers:

- returning cached forecast data without calling the external API;
- retrieving and normalizing forecast data after a cache miss;
- writing normalized forecast data to cache with the expected cache metadata;
- rejecting invalid coordinates before performing an HTTP request;
- handling HTTP failures;
- handling invalid provider responses;
- storing, retrieving, and deleting forecast operational status through ForecastStatusStorage;
- recording cron execution timestamps;
- scheduling forecast refresh queue items;
- preventing duplicate forecast refresh queue items when work is already pending;
- invalidating cached forecast data before a queued refresh;
- recording the timestamp of a successful queued forecast refresh;
- suspending queue processing when forecast retrieval fails;
- normalizing Open-Meteo geocoding responses into internal location data;
- avoiding geocoding HTTP requests for invalid search queries;
- handling empty geocoding results and HTTP failures;
- dispatching the Weather alert created event only for Weather alert nodes;
- handling the Weather alert created event through the event subscriber;

Kernel tests are implemented with Drupal's `KernelTestBase` and are used to verify integrations between Drupal services without requiring a complete browser-based Drupal installation.

The current kernel test suite covers:

- invalidation of forecast cache entries when `nome_modulo.settings` is saved;
- preservation of forecast cache entries when unrelated configuration is saved;
- integration between the Configuration API, `WeatherEventSubscriber`, cache tags, and the forecast cache backend;
- installation of the Weather alert content type;
- installation and configuration of Weather alert fields, widgets, and formatters;
- enforced configuration dependencies for Weather alert field storage;
- Weather alert date-range validation;

Functional tests are implemented with Drupal's `BrowserTestBase`.

The current functional test suite covers:

- access to the forecast page with and without the required permission;
- the default summary display mode;
- valid summary and extended route parameters;
- rejection of invalid display route parameters with an HTTP 404 response;
- forecast rendering using a test double instead of the external Open-Meteo API;
- access to the administrative settings form;
- location search through the geocoding service;
- multiple candidates for ambiguous location searches;
- validation of short and numeric-only location searches;
- persistence of a selected geocoded location, including coordinates and timezone;
- preservation of the current location when only forecast settings are changed;
- forecast-day boundary validation;
- persistence of forecast length and temperature unit;
- forecast block access control;
- default and extended forecast block rendering;
- configurable forecast block display length;
- optional rendering of the full forecast link.

Functional JavaScript tests are implemented with Drupal's `WebDriverTestBase` and use Selenium to verify browser-side JavaScript behavior.

The current functional JavaScript test suite covers:

- expanding the summary forecast through the JavaScript toggle;
- revealing the extended forecast details;
- updating the toggle label and `aria-expanded` state;
- applying the expanded state class to the forecast component;
- collapsing the forecast back to its initial state.

Run the unit tests from the Drupal project root with:

```bash
ddev exec ./vendor/bin/phpunit -c phpunit.xml web/modules/custom/nome_modulo/tests/src/Unit
```

Run the kernel tests from the Drupal project root with:

```bash
ddev exec ./vendor/bin/phpunit -c phpunit.xml web/modules/custom/nome_modulo/tests/src/Kernel
```

Run the functional tests from the Drupal project root with:

```bash
ddev exec ./vendor/bin/phpunit -c phpunit.xml web/modules/custom/nome_modulo/tests/src/Functional
```

Functional JavaScript tests require a Selenium-compatible browser environment. The local DDEV environment used for this project uses the `ddev-selenium-standalone-chrome` add-on.

Run the functional JavaScript tests from the Drupal project root with:

```bash
ddev exec ./vendor/bin/phpunit -c phpunit.xml web/modules/custom/nome_modulo/tests/src/FunctionalJavascript
```

Run the complete automated test suite with:

```bash
ddev exec ./vendor/bin/phpunit -c phpunit.xml web/modules/custom/nome_modulo/tests/src/Unit web/modules/custom/nome_modulo/tests/src/Kernel web/modules/custom/nome_modulo/tests/src/Functional web/modules/custom/nome_modulo/tests/src/FunctionalJavascript
```

## Documentation

Documentation is considered part of the development process rather than a final project deliverable.

This README provides the high-level documentation of the module, while source-code documentation is maintained through meaningful PHPDoc/DocBlock comments where appropriate.

API documentation is generated automatically from the source code with phpDocumentor using the configuration defined in `phpdoc.dist.xml`.

Docker is required to generate the documentation locally.

From the module root, run:

```bash
docker run --rm -v "$(pwd):/data" phpdoc/phpdoc:3 --config=phpdoc.dist.xml
```

The generated documentation and phpDocumentor cache are stored in the directories configured in `phpdoc.dist.xml`.

## License

This project is released under the GNU General Public License, version 2 or later (GPL-2.0-or-later).
