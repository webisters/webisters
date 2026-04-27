<?php
/*
 * This file is part of Webisters Command Line Tool.
 *
 * (c) Hafiz Muhammad Moaz <thewebisters@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (is_file(__DIR__ . '/../../autoload/src/Preloader.php')) {
    require __DIR__ . '/../../autoload/src/Preloader.php';
} else {
    require __DIR__ . '/../vendor/webisters/autoload/src/Preloader.php';
}

use Framework\Autoload\Preloader;

(new Preloader())->load();
