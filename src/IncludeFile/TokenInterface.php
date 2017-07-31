<?php
declare(strict_types=1);
namespace Helhum\Typo3ConsolePlugin\IncludeFile;

/*
 * This file is part of the typo3 console plugin package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface TokenInterface
{
    /**
     * The name of the token that shall be replaced
     *
     * @return string
     */
    public function getName(): string;

    /**
     * The content the token should be replaced with
     *
     * @return string
     */
    public function getContent(): string;
}
