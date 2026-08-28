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
* static analysis;
* automated tests;
* API documentation generated from the source code;
* continuous integration with GitHub Actions will be introduced as the project evolves;

## Documentation

Documentation is considered part of the development process rather than a final project deliverable.

This README will provide the high-level documentation of the module, while source-code documentation will be maintained through meaningful PHPDoc/DocBlock comments where appropriate.

API documentation will be generated automatically from the source code as the project evolves.

## Testing

Automated tests will be added alongside the corresponding functionality.

Testing instructions and coverage details will be documented here once the first test suite is introduced.

## License

This project is released under the GNU General Public License, version 2 or later (GPL-2.0-or-later).
