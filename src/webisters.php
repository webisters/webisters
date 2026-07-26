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

use Framework\CLI\Console;
use Webisters\Commands\Index;
use Webisters\Commands\ListCommand;
use Webisters\Commands\MakeController;
use Webisters\Commands\MakeModel;
use Webisters\Commands\MakeView;
use Webisters\Commands\NewApi;
use Webisters\Commands\NewApp;
use Webisters\Commands\NewOne;
use Webisters\Commands\NewSite;
use Webisters\Commands\RouteList;
use Webisters\Commands\SelfUpdate;
use Webisters\Commands\Setup;

$console = new Console();
$console->addCommand(Index::class);
$console->addCommand(ListCommand::class);
$console->addCommand(Setup::class);
$console->addCommand(RouteList::class);
$console->addCommand(MakeView::class);
$console->addCommand(MakeModel::class);
$console->addCommand(MakeController::class);
$console->addCommand(NewApp::class);
$console->addCommand(NewApi::class);
$console->addCommand(NewOne::class);
$console->addCommand(NewSite::class);
$console->addCommand(SelfUpdate::class);
$console->run();
