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

The current implementation includes:

- a Drupal route handled by `ForecastController`;
- a dedicated `ForecastClient` service;
- constructor dependency injection through `ForecastClientInterface`;
- a preliminary static forecast response, used before integrating the external weather API;
- functional tests covering both authorized and unauthorized access to the forecast page.

The forecast service currently returns static data. Integration with the Open-Meteo API will be introduced in a subsequent development step.

## Requirements

* Drupal 11
* Composer
* Drush

The local development environment used for this project is based on DDEV.

## Installation

The module is currently under development.

Once a functional version is available, installation instructions will be documented here.

## Development

The module is being developed incrementally, with small commits intended to keep the Git history readable and to make the evolution of the project easy to follow.

Code quality and development tooling will include:

* Drupal Coding Standards;
* PHP_CodeSniffer and Drupal Coder;
* automated functional tests;
* source-code documentation through PHPDoc/DocBlock comments.

Additional development tooling will be introduced as the project evolves, including:
* static analysis;
* generated API documentation;
* continuous integration with GitHub Actions;

## Code quality

The module follows Drupal coding standards and is checked with PHP_CodeSniffer and Drupal Coder.

From the Drupal project root:

```bash
ddev exec ./vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom/nome_modulo
```

To automatically fix supported coding standard violations across the module, run:

```bash
ddev exec ./vendor/bin/phpcbf --standard=Drupal,DrupalPractice web/modules/custom/nome_modulo
```

After running PHP Code Beautifier and Fixer, review the changes with:

```bash
git diff
```

and run PHP_CodeSniffer again to verify that no violations remain.

## Testing

Functional tests are implemented with Drupal's `BrowserTestBase`.

The current test suite verifies that:

- users without the required `access content` permission receive an HTTP `403` response;
- users with the required permission receive an HTTP `200` response;
- the forecast page renders the expected title and forecast data.

Run the functional tests from the Drupal project root with:

```bash
ddev exec ./vendor/bin/phpunit -c phpunit.xml web/modules/custom/nome_modulo/tests/src/Functional/ForecastPageTest.php
```
## Documentation

Documentation is considered part of the development process rather than a final project deliverable.

This README will provide the high-level documentation of the module, while source-code documentation will be maintained through meaningful PHPDoc/DocBlock comments where appropriate.

API documentation will be generated automatically from the source code as the project evolves.

## License

This project is released under the GNU General Public License, version 2 or later (GPL-2.0-or-later).
