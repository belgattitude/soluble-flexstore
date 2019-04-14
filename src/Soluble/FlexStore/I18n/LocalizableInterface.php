<?php

/*
 * soluble-flexstore library
 *
 * @author    Vanvelthem Sébastien
 * @link      https://github.com/belgattitude/soluble-flexstore
 * @copyright Copyright (c) 2016-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-flexstore/blob/master/LICENSE.md
 *
 */

namespace Soluble\FlexStore\I18n;

interface LocalizableInterface
{
    /**
     * Get locale.
     *
     * @return string $locale
     */
    public function getLocale(): ?string;

    /**
     * Set locale.
     *
     * @param string $locale
     */
    public function setLocale(?string $locale);
}
