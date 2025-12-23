# Tic-Tac-Toe

A real-time multiplayer Tic-Tac-Toe game built with Laravel and Vue.js. Play against a bot or challenge friends online with shareable game codes.

## Features

- **Two Game Modes**
  - **Bot Mode**: Play against an AI opponent with strategic move logic
  - **Online Mode**: Create a game and share a 6-character code with a friend

- **Real-Time Multiplayer**: Instant updates via WebSockets - see your opponent's moves as they happen

- **Turn Timers**: Optional 5, 10, or 30 second turn limits with auto-forfeit

- **In-Game Chat**: Send messages to your opponent during online matches

- **Score Tracking**: Keep track of wins across multiple rounds

- **Rematch System**: Quick rematch with automatic side swapping

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Vue 3, TypeScript, Tailwind CSS 4
- **Real-Time**: Laravel Reverb (WebSockets)
- **Build**: Vite

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 20+ and npm
- SQLite (default) or MySQL/PostgreSQL

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/tictactoe.git
   cd tictactoe
   ```

2. **Run the setup command**
   ```bash
   composer setup
   ```
   This will:
   - Install PHP dependencies
   - Create `.env` file from `.env.example`
   - Generate application key
   - Run database migrations
   - Install npm dependencies
   - Build frontend assets

3. **Configure environment** (optional)

   The default configuration uses SQLite and works out of the box. If you want to use MySQL or PostgreSQL, update your `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tictactoe
   DB_USERNAME=root
   DB_PASSWORD=
   ```

## Running the Application

Start the development server:
```bash
composer dev
```

This launches all required services concurrently:
- Laravel development server (http://localhost:8000)
- Queue worker for background jobs
- Laravel Pail for log tailing
- Laravel Reverb WebSocket server
- Vite dev server for hot module replacement

Visit **http://localhost:8000** in your browser to play!

## How to Play

### Bot Mode
1. Enter your name on the home page
2. Select "Bot" mode
3. Click "Start Game"
4. You play as X and go first

### Online Mode
1. **Creating a game**: Enter your name, select "Online" mode, and click "Start Game"
2. **Share the code**: You'll see a 6-character game code (e.g., `ABCDEF`)
3. **Joining a game**: Your friend enters the code on the home page and clicks "Join Game"
4. **Play**: Take turns placing X's and O's. First to get 3 in a row wins!

## Development

### Running Tests
```bash
composer test
```

### Code Quality
```bash
# JavaScript/TypeScript linting
npm run lint

# Code formatting
npm run format

# PHP code style
./vendor/bin/pint
```

### Building for Production
```bash
npm run build
```

For SSR (Server-Side Rendering):
```bash
npm run build:ssr
composer dev:ssr
```

## Project Structure

```
├── app/
│   ├── Http/Controllers/GameController.php  # Game actions
│   ├── Services/
│   │   ├── GameService.php                  # Game rules & win detection
│   │   └── BotService.php                   # AI opponent logic
│   └── Events/                              # WebSocket broadcast events
├── resources/js/
│   ├── pages/Game.vue                       # Main game interface
│   ├── components/game/                     # Game UI components
│   └── composables/useTimer.ts              # Turn timer logic
└── tests/
    ├── Feature/GameTest.php                 # Game integration tests
    └── Unit/                                # Unit tests for services
```

## License

MIT
