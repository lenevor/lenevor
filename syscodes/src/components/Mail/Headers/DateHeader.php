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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Mail\Headers;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * A Date MIME Header.
 */
final class DateHeader extends BaseHeader
{
    /**
     * Get the datetime for send a message.
     * 
     * @var \DateTimeImmutable $dateTime
     */
    protected DateTimeImmutable $dateTime;
    
    /**
     * Constructor. Create a new DateHeader class instance
     * 
     * @param  string  $name
     * @param  \DateTimeInterface  $date
     * 
     * @return void
     */
    public function __construct(string $name, DateTimeInterface $date)
    {
        parent::__construct($name);

        $this->setDateTime($date);
    }
    
    /**
     * Set the body content.
     * 
     * @param  DateTimeInterface  $body
     * 
     * @return void
     */
    public function setBody(mixed $body): void
    {
        $this->setDateTime($body);
    }
    
    /**
     * Get the body content.
     * 
     * @return \DateTimeImmutable
     */
    public function getBody(): DateTimeImmutable
    {
        return $this->getDateTime();
    }
    
    /**
     * Get the date-time of the Date in this Header.
     * 
     * @return \DateTimeImmutable
     */
    public function getDateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }
    
    /**
     * Set the date-time of the Date in this Header.
     * 
     * @param  \DateTimeInterface  $dateTime
     * 
     * @return void
     */
    public function setDateTime(DateTimeInterface $dateTime): void
    {
        $this->dateTime = DateTimeImmutable::createFromInterface($dateTime);
    }
    
    /**
     * Get the body as string.
     * 
     * @return string
     */
    public function getBodyAsString(): string
    {
        return $this->dateTime->format(DateTimeInterface::RFC2822);
    }
}