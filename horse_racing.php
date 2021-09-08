<?php

    class Horses {

        private array $horses = [1 => '@', 2 => '#'];

        public function __construct(int $noOfHorses){
            if($noOfHorses > 1){
                for($i = 1; $i <= $noOfHorses; $i++){
                    $this->horses[$i] = readline("Choose the symbol for the horse: ");
                }
            }
        }

        public function getHorses(): array {
            return $this->horses;
        }

        public function unsetHorse(string $horse): void {
            $horseRemove = array_search($horse, $this->horses);
            unset($this->horses[$horseRemove]);
        }

        public function checkHowManyRunning(): int {
            return count($this->horses);
        }

    }

    class Game {
        private array $grid = [];
        private array $horsePositionOnRunway;
        private array $winners = [];
        private int $runwayLength = 45;
        private Horses $horses;
        private int $speed = 125000;

        public function __construct(Horses $horses) {
            $this->horses = $horses;
            $this->horsePositionOnRunway = array_fill_keys($horses->getHorses(), 0);
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

        public function getSpeed(): int {
            return $this->speed;
        }

        public function getRunwayLength(): int {
            return $this->runwayLength;
        }

        public function run(string $horse): void {
            $this->grid[$horse][$this->horsePositionOnRunway[$horse]] = "-";
            $this->horsePositionOnRunway[$horse] += rand(1, 2);
            $this->grid[$horse][$this->horsePositionOnRunway[$horse]] = "$horse";
        }

        public function findWinner(string $horse): void {
            if($this->horsePositionOnRunway[$horse] >= $this->runwayLength){
                $this->winners[] = $horse;
                $this->horses->unsetHorse($horse);
            }
        }
    }

    class DrawGame{
        private string $runway;
        private Game $game;
        private string $side;

        public function __construct(Game $game){
            $this->game = $game;
        }

        public function createBoard(array $grid): void {
            $draw = $this->side;

            foreach($grid as $player => $board){
                $draw .= "$player | ";
                foreach($board as $step){
                    $draw .= "$step ";
                }
                $draw .= "\n";
            }
            $draw .= $this->side;
            $this->runway = $draw;
        }

        public function getRunway(): string {
            return $this->runway;
        }

        public function drawSide(): void {
            $side = '';
            for($i = -1; $i <= $this->game->getRunwayLength(); $i++){
                $side .= "##";
            }
            $side .= "\n";
            $this->side = $side;
        }

    }

    class Bet {
        private int $cash;
        private array $coefficients = [];

        private array $placedBets = [];

        private int $winnings = 0;

        public function __construct(Horses $horses) {
            $this->cash = (int) readline("How much money do you have? ($) ");
            foreach($horses->getHorses() as $index => $horse){
                $this->coefficients[$index] = rand(1, 10);
                $this->placedBets[$index] = 0;
            }
        }

        public function placeBet(int $input, int $horse): void {
            $this->placedBets[$horse] = $input;
        }

        public function getCash(): int {
            return $this->cash;
        }

        public function setCash(int $input): void {
            $this->cash += $input;
        }

        public function getPlacedBets(): array {
            return $this->placedBets;
        }

        public function getCoefficient(int $winner): int {
            return $this->coefficients[$winner];
        }

        public function getWinnings(): int {
            return $this->winnings;
        }

        public function setWinnings($input): void {
            $this->winnings += $input;
        }

        public function calculateWinnings(int $index): int {
            return $this->coefficients[$index] * $this->placedBets[$index];
        }

    }

    $noOfHorses = (int) readline("How many horses will participate? ");

    $horses = new Horses($noOfHorses);
    $game = new Game($horses);
    $board = new DrawGame($game);
    $bets = new Bet($horses);

    foreach($horses->getHorses() as $index => $horse){
        echo "$index | Horse $horse\n";
    }

    //Save array in this variable because unset horses after the race
    $racers = $horses->getHorses();
    $board->drawSide();

    while(true){
        echo "Your cash: \${$bets->getCash()}\n";
        $betHorse = readline("Choose a horse to place a bet on or press ENTER to start the race: ");
        if(array_key_exists($betHorse, $horses->getHorses())){
            $bet = (int) readline("Place a bet for the horse [{$horses->getHorses()[$betHorse]}]\n");
            if($bet > $bets->getCash()) {
                echo "Not enough money!\n";
                continue;
            }
            $bets->placeBet($bet, $betHorse);
            $bets->setCash(-$bet);
        }
        if(strtoupper($betHorse) === ""){
            break;
        }
    }

    while(true){

        $board->createBoard($game->getGrid());
        echo $board->getRunway();
        echo PHP_EOL . PHP_EOL . PHP_EOL;

        if($horses->checkHowManyRunning() === 0) break;
        foreach($horses->getHorses() as $horse){
            $game->run($horse);
            $game->findWinner($horse);
        }

        usleep($game->getSpeed());

    }

    usleep(500000);
    echo PHP_EOL;

    foreach($game->getWinners() as $position => $horse){
        $position++;
        echo "$position place: Horse '$horse'\n";
    }

    $noBetsPlaced = 0;
    foreach($bets->getPlacedBets() as $index => $bet){
        if($bet > 0 && $game->getWinners()[0] === $racers[$index]){
            $bets->setWinnings($bets->calculateWinnings($index));
            echo "Congratulations! You won: \${$bets->getWinnings()}\n";
            echo "Horse winning coefficient was {$bets->getCoefficient($index)}\n";
            $bets->setCash($bets->getWinnings());
            die("Your cash: {$bets->getCash()}");
        }
        if($bet > 0) $noBetsPlaced++;
    }

    if($noBetsPlaced === 0) die("You didn't place a bet! What a shame!");
    echo "You lost! Your cash \${$bets->getCash()}";