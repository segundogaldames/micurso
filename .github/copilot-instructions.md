# Copilot instructions for AxiomaFrame (micurso)

Purpose
- Help AI coding agents be productive in this repository (AxiomaFrame-based PHP app).

Quick architecture summary
- Front controller: `public/index.php` — all requests go through this file.
- Request parsing: `application/Request.php` reads the `?url=` GET parameter and splits into [module]/controller/method/args.
- Dispatcher: `application/Bootstrap.php` locates and instantiates controllers under the `controllers\\` namespace (or `modules\\<mod>\\controllers\\`).
- Controllers: live in `controllers/` and are classes named `XController` (namespace `controllers`). Methods are actions; default method is `index`.
- Views: Twig templates in `views/`. Use `application/View->load('path/name', $params)`; pass Twig path without `.twig` (e.g. `courses/courses`).
- Models: Eloquent (Illuminate) is used. DB is configured in `application/Database.php` and models extend `Illuminate\\Database\\Eloquent\\Model` (see `models/User.php`).

Conventions & project-specific patterns
- Controller base class: `application/Controller.php` — contains helpers: `validateForm()`, `validateDelete()`, `validatePUT()`, `validateSession()`, encryption helpers and `redirect()`.
- CSRF/form token: controllers expect a `send` field encrypted with `Controller::getForm()`; verification uses `Filter::getPostRaw('send')` then `decrypt()`.
- HTTP method override: forms use `_method` (POST with `_method=DELETE` or `PUT`); controller helpers check it.
- Flash & session: use `application/Flash.php` and `application/Session.php` (global Twig var `flash_messages`).
- Views: render with `$this->_view->load('folder/view', ['key'=>value]);` (not `render`).
- Routing example: URL `/courses/list/1` -> controller `controllers\\CoursesController::list(1)` (see `application/Request.php` and `application/Bootstrap.php`).

Developer workflows
- Install deps: `composer install` (uses `vendor/` already present). If Eloquent issues appear, run `composer update` as noted in README.
- Dev server (quick):
  - `php -S localhost:8000 -t public` and open `http://localhost:8000/?url=controller/method`
  - Or configure Apache/Nginx to point document root to the `public/` folder (production expected).
- No automated tests detected in repo — run manual checks and use browser to verify routes.

Where to look first when changing behavior
- Routing/dispatch: `application/Request.php`, `application/Bootstrap.php`.
- Controller behavior and shared helpers: `application/Controller.php`.
- DB bootstrapping: `application/Database.php` and `models/` for Eloquent usage.
- View rendering and global vars: `application/View.php` and `views/` templates.
- Config values: `application/Config.php` and `application/Config.example.php` (constants like `DEFAULT_CONTROLLER`, `BASE_URL`, etc.).

Examples (copy/paste)
- Minimal controller:

```php
<?php
namespace controllers;

class HelloController extends \\application\\Controller
{
    public function index()
    {
        $this->_view->load('index/index', ['msg' => 'Hello']);
    }
}
```

- Load a model (Eloquent):
```php
use models\\User;
$user = User::find(1);
```

Notes for the agent
- Only change files within the repo when asked; follow existing naming and namespace conventions.
- Keep view paths relative to `views/` and call `View->load()`.
- Preserve the CSRF `send` flow unless explicitly refactoring both form generation and verification.
- When adding routes/controllers, ensure file name, class name and namespace match the dispatch logic in `Bootstrap.php`.

If something here is unclear or you'd like more examples (e.g., common controller patterns, form structures, or how modules behave), tell me which area to expand.
