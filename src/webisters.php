#!/usr/bin/env php
<?php
/*
 * This file is part of Webisters Command Line Tool.
 *
 * (c) Hafiz Muhammad Moaz <thewebisters@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (is_file(__DIR__ . '/../../../autoload.php')) {
    require __DIR__ . '/../../../autoload.php';
} else {
    require __DIR__ . '/../vendor/autoload.php';
}

use Webisters\Commands\Index;
use Webisters\Commands\RouteList;
use Webisters\Commands\MakeView;
use Webisters\Commands\MakeModel;
use Webisters\Commands\MakeController;
use Webisters\Commands\NewApp;
use Webisters\Commands\NewApi;
use Webisters\Commands\NewOne;
use Framework\CLI\Console;

$console = new Console();
$console->addCommand(Index::class);
$console->addCommand(RouteList::class);
$console->addCommand(MakeView::class);
$console->addCommand(MakeModel::class);
$console->addCommand(MakeController::class);
$console->addCommand(NewApp::class);
$console->addCommand(NewApi::class);
$console->addCommand(NewOne::class);
$console->run();
