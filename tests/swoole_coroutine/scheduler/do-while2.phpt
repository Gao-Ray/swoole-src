--TEST--
swoole_coroutine/scheduler: do-while tick 1000 without opcache enable
--SKIPIF--
<?php
require __DIR__ . '/../../include/skipif.inc';
skip_if_constant_not_defined('SWOOLE_CORO_SCHEDULER_TICK');
skip_if_ini_bool_equal_to('opcache.enable_cli', true);
?>
--FILE--
<?php
require __DIR__ . '/../../include/bootstrap.php';

declare(ticks=1000);

$max_msec = 10;
Co::set(['max_exec_msec' => $max_msec]);

$start = microtime(1);
echo "start\n";
$flag = 1;

go(function () use (&$flag) {
    echo "coro 1 start to loop\n";
    $i = 0;
    do {
        $i++;
    } while ($flag);
    echo "coro 1 can exit\n";
});

$end = microtime(1);
$msec = ($end - $start) * 1000;
USE_VALGRIND || Assert::lessThanEq(abs($msec - $max_msec), 2);

go(function () use (&$flag) {
    echo "coro 2 set flag = false\n";
    $flag = false;
});
echo "end\n";

Swoole\Event::wait();
?>
--EXPECTF--
start
coro 1 start to loop
coro 2 set flag = false
end
coro 1 can exit
