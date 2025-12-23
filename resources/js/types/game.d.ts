export type TimerSetting = 'off' | '5' | '10' | '30';
export type GameMode = 'bot' | 'online';
export type PlayerSymbol = 'X' | 'O';
export type GameStatus = 'waiting' | 'playing' | 'finished';
export type Winner = 'X' | 'O' | 'draw' | null;

export interface Game {
    id: number;
    code: string;
    board: string[];
    mode: GameMode;
    timer_setting: TimerSetting;
    player_x_name: string;
    player_x_score: number;
    player_o_name: string | null;
    player_o_score: number;
    current_turn: PlayerSymbol;
    status: GameStatus;
    winner: Winner;
    turn_started_at: string | null;
    winning_cells?: number[];
    rematch_requested_by?: PlayerSymbol | null;
}

export interface ChatMessage {
    id: number;
    player_name: string;
    player_symbol: PlayerSymbol | null;
    content: string;
    created_at: string;
    is_system?: boolean;
}

export interface GamePageProps {
    game: Game;
    playerSymbol: PlayerSymbol | null;
    messages: ChatMessage[];
}
