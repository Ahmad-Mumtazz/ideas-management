# Project Instructions

## Existing Project

This is an existing Laravel application.

Do not recreate the project from scratch.

Before modifying existing functionality:

* Inspect and understand the relevant code.
* Preserve working functionality.
* Reuse existing code where appropriate.
* Avoid unnecessary rewrites or replacements.
* Do not make destructive changes without understanding their impact.

## Packages

Use Laravel's existing functionality whenever possible.

Do not install unnecessary packages.

Do not install Laravel Boost unless the user explicitly requests or approves it.

## Authentication

The project already contains authentication functionality.

Do not recreate authentication unnecessarily.

Only modify the existing authentication system when required for a legitimate bug, security issue, or project requirement.

## Security

Always enforce authorization on the server side.

User-owned resources must only be accessible by their owner.

Never rely solely on frontend restrictions or hidden UI elements.

Pay particular attention to:

* IDOR / unauthorized record access
* mass assignment
* unsafe file uploads
* unauthorized updates/deletions
* insecure file access
* missing validation
* CSRF protection

## Laravel Conventions

Prefer standard Laravel features and conventions, including:

* Eloquent relationships
* Form Requests where appropriate
* Policies
* middleware
* route model binding
* named routes
* Blade components
* Laravel Storage
* migrations
* appropriate database constraints

Avoid unnecessary architectural complexity.

## Validation

Validate all user-controlled input.

Do not trust values supplied by the browser.

## Existing Code

Do not modify files merely for the sake of refactoring.

When changing existing code, make focused changes and preserve unrelated functionality.

## Testing

After implementing changes, run appropriate tests and verify the affected functionality.

Do not consider a feature complete merely because the page renders.

## Audit Mode

When the user explicitly asks for an audit:

* Work in read-only mode.
* Do not modify application code.
* Do not install packages.
* Do not create migrations.
* Do not fix issues yet.
* Inspect the existing implementation.
* Report what is completed, partially completed, missing, and problematic.
* Wait for the user's approval before implementing changes.

## Communication

When making changes, clearly explain:

* what was changed
* why it was changed
* which files were affected
* what was tested
* any remaining issues

Do not claim functionality is complete without verifying it.
