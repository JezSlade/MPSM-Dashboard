<?php
abstract class BaseModule implements ModuleInterface {
    protected array $config;
    public function __construct(array $config = []) {
        $this->config = $config;
    }
    public static function describe(): array {
        return ['name'=>static::class,'description'=>''];
    }
}
