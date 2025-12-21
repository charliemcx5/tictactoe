# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build and Development Commands

```bash
# Setup (initial install)
composer setup

# Development (runs server, queue, logs, and vite concurrently)
composer dev

# Development with SSR
composer dev:ssr

# Run tests
composer test

# Run a single test file
./vendor/bin/pest tests/Feature/Auth/AuthenticationTest.php

# Run a specific test by name
./vendor/bin/pest --filter="test name"

# Frontend only
npm run dev          # Vite dev server
npm run build        # Production build
npm run build:ssr    # SSR build

# Code quality
npm run lint         # ESLint with auto-fix
npm run format       # Prettier format
npm run format:check # Check formatting
./vendor/bin/pint    # PHP code style (Laravel Pint)
```

## Architecture Overview

This is a Laravel 12 application using Inertia.js with Vue 3 (TypeScript) for the frontend.

### Tech Stack
- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Vue 3 + TypeScript, Inertia.js, Tailwind CSS 4
- **Auth**: Laravel Fortify with 2FA support (TOTP)
- **Real-time**: Laravel Reverb (WebSocket broadcasting)
- **Testing**: Pest PHP with Laravel plugin

### Key Directories
- `app/Actions/Fortify/` - Authentication actions (registration, password reset)
- `app/Http/Controllers/Settings/` - User settings controllers (profile, password, 2FA)
- `resources/js/pages/` - Vue page components (mapped to routes via Inertia)
- `resources/js/components/` - Reusable Vue components
- `resources/js/components/ui/` - UI component library (shadcn-vue style)
- `resources/js/layouts/` - Page layout components
- `resources/js/composables/` - Vue composables (useAppearance, useTwoFactorAuth)
- `resources/js/types/` - TypeScript type definitions

### Inertia Page Resolution
Pages are resolved from `resources/js/pages/` matching the Inertia::render() name. Example: `Inertia::render('Dashboard')` resolves to `resources/js/pages/Dashboard.vue`.

### Route-to-Type Generation
Uses `@laravel/vite-plugin-wayfinder` to generate typed route helpers in `resources/js/wayfinder/`.

### Testing
Tests use Pest with RefreshDatabase trait for Feature tests. Feature tests are in `tests/Feature/`, unit tests in `tests/Unit/`.
