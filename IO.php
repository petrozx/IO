<?php

use JetBrains\PhpStorm\NoReturn;

interface Monad
{
    public function fMap(callable $IOfn): Monad;

    public function map(callable $fn): Monad;
}

class ResolveDone
{
    private static self $self;

    #[NoReturn] public function __invoke($status=null, $text=null, $mess=null): bool|string
    {
        return json_encode([
            'status' => $status,
            'body' => $text,
            'message' => $mess
        ]);
    }

    private function __construct()
    {}

    public static function oo($status=null, $text=null, $mess=null): bool|string
    {
        if(empty(self::$self)) {
            self::$self = new self();
        }
        return (self::$self)($status, $text, $mess);
    }
}

class BreakExit
{
    private static self $self;

    #[NoReturn] public function __invoke($text=''): void
    {
        exit($text);
    }

    private function __construct()
    {}

    public static function ee($text='')
    {
        if(empty(self::$self)) {
            self::$self = new self();
        }
        return (self::$self)($text);
    }
}

class IO implements Monad
{

    private static int $m=0;
    private static int $f=0;
    private static int $fg=0;
    private static int $mg=0;


    private function __construct(
        private $value,
        private array $runtime = [],
    ){}

    private function isEmpty(&$var): bool
    {
        return !($var || (is_scalar($var) && strlen($var)));
    }

    public static function cc($value=null): Monad
    {
        return new self($value);
    }

    public function is_empty(): bool
    {
        return $this->isEmpty($this->value);
    }

    public function fMap(callable $IOfn): Monad
    {
        try {
                $runtime = [...$this->runtime, ['fMap', $IOfn]];
                return new IO($this->value, $runtime);
        }catch (Exception $exception) {
            BreakExit::ee(
                ResolveDone::oo(false, null, $this->excText($exception))
            );
        }
    }

    public function map(callable $fn): Monad
    {
        try {
                $runtime = [...$this->runtime, ['map', $fn]];
                return new IO($this->value, $runtime);
        }catch (Exception $exception) {
            BreakExit::ee(
                ResolveDone::oo(false, null, $this->excText($exception))
            );
        }
    }

    public function get($method='')
    {
        if ($method === 'fMap') self::$fg += 1;
        else if ($method === 'map') self::$mg += 1;
        return $this->value;
    }

    public function __toString()
    {
        return self::class;
    }

    public function __invoke(): bool|string
    {
        return $this->run();
    }

    private function excText($exception): string
    {
        return "Exception: ". $exception->getMessage().
            " on line ".$exception->getLine();
    }

    private function run(): bool|string
    {
        while ([$method, $fn] = array_shift($this->runtime)) {
            set_exception_handler(fn($exception) =>
                BreakExit::ee(
                    ResolveDone::oo(false, null, $this->excText($exception))
                )
            );
            try {
                $val = $fn($this->value);
                $cl = get_class(is_integer($val)?new StdClass():$val);
                if (method_exists($cl, 'run')){
                    $val->run();
                }
                if (method_exists($cl, 'get')){
                    $val = $val->get($method);
                }
                if ($method === 'fMap') {
                    self::$f += 1;
                    if (self::$fg !== self::$f || gettype($this->value) !== gettype($val)) {
                        BreakExit::ee(
                            ResolveDone::oo(false, null, 'Типы не совпадают')
                        );
                    }
                } else if ($method === 'map') {
                    self::$m += 1;
                    if (self::$mg === self::$m) {
                        BreakExit::ee(
                            ResolveDone::oo(false, null, 'Типы не совпадают')
                        );
                    }
                }
                $this->value = $val;
            } catch (Exception $e) {
                BreakExit::ee(
                    ResolveDone::oo(false)
                );
            }
        }
        return ResolveDone::oo(true, $this->value);
    }
}

$vbl = IO::cc(6);
$vbl1 = IO::cc(7);
$vbl2 = IO::cc(7);
$vbl3 = IO::cc(3);

$res = $vbl->map(function ($v1) use ($vbl1, $vbl2) {
    return $vbl1->map(function ($v2) use ($vbl2, $v1) {
        return $vbl2->map(fn($v3)=>$v1+$v2+$v3);
    });
});

$res1 = $res->fMap(function ($v) use ($vbl3) {
    return $vbl3->fMap(function($n) use ($v) {
        return IO::cc($v + $n);
    });
});

var_dump($res1());
