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
 * Class Doctor.
 *
 * @package webisters
 */
class Doctor extends Check
{
    protected string $name = 'doctor';
    protected string $usage = 'doctor';
}
