<?php declare(strict_types=1);
/*
 * This file is part of Webisters Command Line Tool.
 *
 * (c) Hafiz Muhammad Moaz <thewebisters@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Webisters\Commands;

/**
 * Class NewApp.
 *
 * @package webisters
 */
class NewApp extends NewCommand
{
    protected string $name = 'new-app';
    protected string $description = 'Creates a new App Project.';
    protected string $usage = 'new-app [options] [directory]';

    public function run() : void
    {
        $this->create('app', 'App Project');
    }
}
