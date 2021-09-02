<?php

    class Horses {

        private array $horses = [1 => '@', 2 => '#', 3 => '$'];

        public function getHorses(): array {
            return $this->horses;
        }

        public function unsetHorse(string $horse){
            $playerRemove = array_search($horse, $this->horses);
            unset($this->horses[$playerRemove]);
        }

        public function checkHowManyRunning(): int {
            return count($this->horses);
        }

    }

    class DrawGame{
        private string $board;

        public function createBoard(array $grid){
            $draw = "";
            foreach($grid as $player => $board){
                $draw .= "$player | ";
                foreach($board as $step){
                    $draw .= "$step ";
                }
                $draw .= "\n";
            }
            $this->board = $draw;
        }

        public function getBoard(): string {
            return $this->board;
        }
    }

    class Game {
        private array $grid = [];
        private array $randomValueForHorses;
        private array $winners = [];
        private int $runwayLength = 25;
        private Horses $horses;

        public function __construct(Horses $horses) {
            $this->horses = $horses;
            $this->randomValueForHorses = array_fill_keys($horses->getHorses(), 0);
            for($gridForHorse = 1; $gridForHorse <= count($horses->getHorses()); $gridForHorse++){
                for($distanceLength = 1; $distanceLength < $this->runwayLength; $distanceLength++){
                    $this->grid[$horses->getHorses()[$gridForHorse]][$distanceLength] = "-";
                }
            }
        }

        public function getGrid(): array {
            return $this->grid;
        }

        public function getWinners(): array {
            return $this->winners;
        }

        public function run(string $horse){
            $this->grid[$horse][$this->randomValueForHorses[$horse]] = "-";
            $this->randomValueForHorses[$horse] += rand(1, 2);
            $this->grid[$horse][$this->randomValueForHorses[$horse]] = "$horse";
        }

        public function findWinner(string $horse) {
            if($this->randomValueForHorses[$horse] >= $this->runwayLength){
                $this->winners[] = $horse;
                $this->horses->unsetHorse($horse);
            }
        }
    }

    class Bet {
        private array $coefficients = [
            10 => 1,
            20 => 2,
            30 => 3
        ];

        private array $placedBets = [
            1 => 0,
            2 => 0,
            3 => 0
        ];

        private int $winnings = 0;

        public function placeBet(int $input, int $horse){
            $this->placedBets[$horse] = $input;
        }

        public function getPlacedBets(): array {
            return $this->placedBets;
        }

        public function getWinnings(): int {
            return $this->winnings;
        }

        public function setWinnings($input) {
            $this->winnings += $input;
        }

        public function calculateWinnings(int $index){
            $coef = array_keys($this->coefficients, $index);
            return $coef[0] * $this->placedBets[$index];
        }

    }

    $horses = new Horses();
    $game = new Game($horses);
    $board = new DrawGame();
    $bets = new Bet();

    foreach($horses->getHorses() as $index => $horse){
        echo "$index | Horse $horse\n";
    }

    $racers = $horses->getHorses();

    while(true){
        $betHorse = readline("Choose a horse to place a bet on or type C to start the race: ");

        if(array_key_exists($betHorse, $horses->getHorses())){
            $bet = (int) readline("Place a bet for the horse [{$horses->getHorses()[$betHorse]}]\n");
            $bets->placeBet($bet, $betHorse);
        }
        if(strtoupper($betHorse) === "C"){
            break;
        }
    }


    $competition = readline("Press ENTER to start the race!");

    while(isset($competition)){
        $board->createBoard($game->getGrid());
        echo $board->getBoard();
        echo PHP_EOL . PHP_EOL . PHP_EOL;
        if($horses->checkHowManyRunning() === 0) break;
        foreach($horses->getHorses() as $horse){
            $game->run($horse);
            $game->findWinner($horse);
        }
        usleep('120000');

    }

    echo PHP_EOL;

    foreach($game->getWinners() as $position => $horse){
        $position++;
        echo "{$position} place: Horse '$horse'\n";
    }

    foreach($bets->getPlacedBets() as $index => $bet){
        if($bet > 0 && $game->getWinners()[0] === $racers[$index]){
            $bets->setWinnings($bets->calculateWinnings($index));
            echo "Congratulations! You won: \${$bets->getWinnings()}\n";
        }
    }

    if($bets->getWinnings() === 0) echo "You lost!";