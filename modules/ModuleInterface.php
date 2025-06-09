<?php
interface ModuleInterface {
    public function __construct(array $config = []);
    public function render(): string;
    public static function describe(): array;
}
