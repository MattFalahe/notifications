<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Seat\Notifications\Jobs\AbstractNotification;

abstract class AbstractMailNotification extends AbstractNotification
{
    public const RATE_LIMIT_KEY = 'mail_notifications';
    public const RATE_LIMIT = 60;

    public function middleware(): array
    {
        return [new RateLimitedWithRedis(self::RATE_LIMIT_KEY)];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * @deprecated 5.0 Child classes should move to using populateMessage instead of overwriting toMail. In the future, toMail will become final.
     *
     * @param  $notifiable
     * @return MailMessage
     */
    // don't type hint this function, it sometimes breaks notification that still override this
    public function toMail($notifiable)
    {
        $message = new MailMessage();
        $this->populateMessage($message, $notifiable);

        return $message;
    }

    /**
     * Populate the content of the notification.
     *
     * @param  MailMessage  $message
     * @param  $notifiable
     * */
    protected function populateMessage(MailMessage $message, $notifiable) {}
}