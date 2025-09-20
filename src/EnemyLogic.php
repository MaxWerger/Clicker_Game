<?php

namespace App;

class EnemyLogic
{
    private string $name;
    private string $level;
    private string $health;
    private string $maxHealth;
    private string $damage;
    private string $reward;

    public function __construct(string $name, string $level, string $health, string $maxHealth, string $damage, string $reward){
        $this->name = $name;
        $this->level = $level;
        $this->health = $health;
        $this->maxHealth = $maxHealth;
        $this->damage = $damage;
        $this->reward = $reward;
    }
    public function getName(): array
    {
        $name =['Dimozaur', 'Maxonux', 'Maganator'];
        return '';
    }

}
