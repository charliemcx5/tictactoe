# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build and Development Commands

```bash
# Setup (initial install)
composer setup

# Development (runs server, queue, logs, reverb, and vite concurrently)
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

This is a real-time multiplayer Tic-Tac-Toe game built with Laravel 12 and Vue 3 (TypeScript) via Inertia.js.

### Tech Stack
- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Vue 3 + TypeScript, Inertia.js, Tailwind CSS 4
- **Auth**: Laravel Fortify with 2FA support (TOTP)
- **Real-time**: Laravel Reverb (WebSocket broadcasting)
- **Testing**: Pest PHP with Laravel plugin

### Key Directories
- `app/Services/` - Business logic (GameService for game rules, BotService for AI opponent)
- `app/Events/` - Broadcast events (GameUpdated, PlayerJoined, ChatMessageSent)
- `app/Http/Controllers/GameController.php` - All game actions (create, join, move, forfeit, rematch)
- `resources/js/pages/Game.vue` - Main game UI with real-time updates
- `resources/js/components/game/` - Game-specific components (GameBoard, PlayerInfo, GameChat)
- `resources/js/types/game.d.ts` - TypeScript types for game state
- `resources/js/composables/useTimer.ts` - Turn timer logic
- `resources/js/components/ui/` - UI component library (shadcn-vue style)

### Game Architecture
- **Session-based player identity**: Players identified via `session('game_player')` containing game_id, symbol, and name
- **Two game modes**: `bot` (vs AI) and `online` (multiplayer with 6-character game codes)
- **Real-time sync**: Uses Laravel Echo with public channels (`game.{code}`) for WebSocket updates
- **Timer system**: Optional turn timers (5/10/30 seconds), auto-forfeit on timeout
- **Rematch flow**: Online games use request/accept pattern with side swapping; bot games reset immediately

### Broadcasting Pattern
Events broadcast on public channels (no auth required). Frontend uses `useEchoPublic` composable to listen for GameUpdated, PlayerJoined, and ChatMessageSent events, then updates local state reactively.

### Inertia Page Resolution
Pages are resolved from `resources/js/pages/` matching the Inertia::render() name. Example: `Inertia::render('Game')` resolves to `resources/js/pages/Game.vue`.

### Route-to-Type Generation
Uses `@laravel/vite-plugin-wayfinder` to generate typed route helpers in `resources/js/wayfinder/`.

### Testing
Tests use Pest with RefreshDatabase trait. Game tests mock session state to simulate player identity. Feature tests in `tests/Feature/`, unit tests in `tests/Unit/`.
