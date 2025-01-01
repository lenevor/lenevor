<?php

/**
 * Lenevor Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.md.
 * It is also available through the world-wide-web at this URL:
 * https://lenevor.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Lenevor.com so we can send you a copy immediately.
 *
 * @package     Lenevor
 * @subpackage  Base
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Mail\Encoder;

/**
 * An IDN email address encoder. Encodes the domain part of an 
 * address using IDN. This is compatible will all SMTP servers.
 */
final class IdnAddressEncoder
{
    /**
     * Encodes the domain part of an address using IDN.
     * 
     * @param  string  $address
     * 
     * @return string
     */
    public function encodeString(string $address): string
    {
        $addr = strrpos($address, '@');
        
        if (false !== $addr) {
            $local  = substr($address, 0, $addr);
            $domain = substr($address, $addr + 1);
            
            if (preg_match('~[^\x00-\x7F]~', $domain)) {
                $address = sprintf('%s@%s', $local, idn_to_ascii($domain, \IDNA_DEFAULT | \IDNA_USE_STD3_RULES | \IDNA_CHECK_BIDI | \IDNA_CHECK_CONTEXTJ | \IDNA_NONTRANSITIONAL_TO_ASCII, \INTL_IDNA_VARIANT_UTS46));
            }
        }
        
        return $address;
    }
}