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
 * Class NewSite.
 *
 * @package webisters
 */
class NewSite extends NewCommand
{
    protected string $name = 'new-site';
    protected string $description = 'Creates a new Static Site Project.';
    protected string $usage = 'new-site [options] [directory]';

    public function run() : void
    {
        $this->create('site', 'Static Site Project');
    }
}
