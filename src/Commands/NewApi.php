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
 * Class NewApi.
 *
 * @package webisters
 */
class NewApi extends NewCommand
{
    protected string $name = 'new-api';
    protected string $description = 'Creates a new API Project.';
    protected string $usage = 'new-api [options] [directory]';

    public function run() : void
    {
        $this->create('api', 'API Project');
    }
}
