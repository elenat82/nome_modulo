# Nome Modulo

`nome_modulo` is a custom module for Drupal 11, developed as a learning and portfolio project.

The project is intended to put into practice the main concepts and APIs involved in modern Drupal module development, following Drupal coding standards and development best practices.

The project name was chosen so that everyone can customize it to their liking.

> **Status:** Work in progress.

## Goals

The main goals of this project are to:

* develop a complete Drupal 11 custom module incrementally;
* apply Drupal coding standards and conventions;
* use object-oriented PHP and Drupal's service container appropriately;
* keep code, configuration, documentation, and tests under version control;
* document architectural and implementation decisions as the project evolves;
* integrate automated code quality checks and documentation generation.

During development, the module will be used to explore and apply topics such as:

* routing, controllers, responses, menu links, and permissions;
* services and dependency injection;
* hooks and events;
* Plugin API and block plugins;
* Form API;
* Render API, Twig templates, translations, and asset libraries;
* Configuration API and State API;
* Entity API, Field API, and validation;
* Database API;
* Cron and Queue API (probably not Batch API);
* cacheability and performance;
* security;
* automated testing.

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
- configurable location, coordinates, timezone, forecast length, and temperature unit through an administrative settings form;
- custom permissions controlling access to the forecast page and administrative settings;
- an administrative menu link for the settings page;
- object-oriented hook implementations using Drupal's `#[Hook]` attribute;
- Render API and a dedicated Twig template for forecast output;
- a Drupal asset library providing component-specific CSS and JavaScript;
- a Drupal JavaScript behavior allowing the summary forecast to reveal the extended forecast without reloading the page;
- a `display` route parameter supporting summary and extended forecast views. In summary mode, the first forecast day is displayed initially and the remaining days can be revealed through a JavaScript toggle. In extended mode, all forecast days are displayed immediately;
- functional tests covering access control, module configuration, route parameters, and forecast rendering.

The default forecast URL:

`/weather`

uses the summary display mode.

The following URLs are also available:

- /weather/summary
- /weather/extended

Only summary and extended are accepted as values for the display route parameter.

## Requirements

* Drupal 11.1 or later
* Composer
* Drush

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

Current code quality and development tooling includes:

* Drupal Coding Standards;
* PHP_CodeSniffer and Drupal Coder;
* automated functional tests;
* source-code documentation through PHPDoc/DocBlock comments.
* generated API documentation through phpDocumentor.

Additional development tooling will be introduced as the project evolves, including:
* static analysis;
* continuous integration with GitHub Actions.

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

## Testing

The project includes automated unit and functional tests.

Unit tests are implemented with Drupal's `UnitTestCase` and are used to verify isolated application logic without performing real HTTP requests.

The current unit test suite covers:

- returning cached forecast data without calling the external API;
- retrieving and normalizing forecast data after a cache miss;
- writing normalized forecast data to cache with the expected cache metadata;
- rejecting invalid coordinates before performing an HTTP request;
- handling HTTP failures;
- handling invalid provider responses.

Functional tests are implemented with Drupal's `BrowserTestBase`.

The current functional test suite covers:

- access to the forecast page with and without the required permission;
- the default summary display mode;
- valid summary and extended route parameters;
- rejection of invalid display route parameters with an HTTP 404 response;
- forecast rendering using a test double instead of the external Open-Meteo API;
- access to the administrative settings form;
- persistence of forecast configuration values, including location, coordinates, timezone, forecast length, and temperature unit;
- custom settings form validation;
- forecast block access control;
- default and extended forecast block rendering;
- configurable forecast block display length;
- optional rendering of the full forecast link.

Run the unit tests from the Drupal project root with:

```bash
ddev exec ./vendor/bin/phpunit -c phpunit.xml web/modules/custom/nome_modulo/tests/src/Unit
```

Run the functional tests from the Drupal project root with:

```bash
ddev exec ./vendor/bin/phpunit -c phpunit.xml web/modules/custom/nome_modulo/tests/src/Functional
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
