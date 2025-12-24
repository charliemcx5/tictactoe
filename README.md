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

- **Backend**: Laravel 12, PHP 8.3+
- **Frontend**: Vue 3, TypeScript, Tailwind CSS 4
- **Real-Time**: Laravel Reverb (WebSockets)
- **Build**: Vite

## Requirements

- PHP 8.3 or higher
- PHP extensions: curl, mbstring, xml, sqlite3 (or mysql/pgsql)
- Composer 2.5+ (installed via official installer, **not** via apt)
- Node.js 20+ and npm
- SQLite (default) or MySQL/PostgreSQL

---

## Installation

### Step 1: Check/Upgrade PHP Version

First, verify your PHP version:

```bash
php -v
```

You need **PHP 8.3.x or higher**. If your version is lower, upgrade PHP:

#### Ubuntu/Debian/Pop!_OS

You can use the included upgrade script:

```bash
chmod +x upgrade-php.sh
./upgrade-php.sh
```

Or manually:

```bash
# Add the PHP repository
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Install PHP 8.3 with required extensions
sudo apt install php8.3 php8.3-cli php8.3-curl php8.3-mbstring php8.3-xml php8.3-sqlite3

# Set PHP 8.3 as default
sudo update-alternatives --set php /usr/bin/php8.3

# Verify
php -v
```

### Step 2: Install Composer (Official Method)

> ⚠️ **Important**: Do NOT use `apt install composer` — the apt version is outdated and causes compatibility issues.

Check if you have Composer installed correctly:

```bash
composer --version
```

You need **Composer 2.5.0 or higher**. If you see version 2.2.x or get deprecation warnings, reinstall Composer:

```bash
# Remove apt-installed composer if present
sudo apt remove composer 2>/dev/null

# Install latest Composer via official installer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer

# Verify (should show 2.8.x or higher)
composer --version
```

### Step 3: Verify All Requirements

Run these checks before proceeding:

```bash
# PHP version (need 8.3+)
php -v

# Composer version (need 2.5+)
composer --version

# Node.js version (need 20+)
node -v

# npm version
npm -v
```

### Step 4: Clone and Setup

```bash
# Clone the repository
git clone https://github.com/your-username/tictactoe.git
cd tictactoe

# Run the setup command
composer setup
```

The setup command will automatically:
- Install PHP dependencies
- Create `.env` file from `.env.example`
- Generate application key
- Run database migrations
- Install npm dependencies
- Build frontend assets

### Step 5: Configure Environment (Optional)

The default configuration uses SQLite and works out of the box. If you want to use MySQL or PostgreSQL, update your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tictactoe
DB_USERNAME=root
DB_PASSWORD=
```

---

## Troubleshooting

### Permission Denied Errors

If you see "Permission denied" errors during setup, fix directory ownership:

```bash
sudo chown -R $USER:$USER /path/to/tictactoe
```
Replace `/path/to/tictactoe` with the actual path to the `tictactoe` directory on your machine.

Then re-run `composer setup`.

### Composer Shows PHP 8.1 Despite PHP 8.3 Installed

This happens when Composer uses a different PHP binary. Fix by running:

```bash
php8.3 $(which composer) setup
```

Or permanently fix by updating the default PHP:

```bash
sudo update-alternatives --set php /usr/bin/php8.3
```

### Deprecation Warnings from Composer

If you see `${var}` deprecation notices, your Composer is outdated. Reinstall using the official installer (see Step 2 above).

---

## Running the Application

Start the development server (use SSR only):

```bash
composer dev:ssr
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
