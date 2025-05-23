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

namespace Seat\Notifications\Notifications\Characters\Mail;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Killmails\KillmailDetail;
use Seat\Notifications\Contracts\ExposesRequiredUniverseIds;
use Seat\Notifications\Jobs\Middleware\LoadRequiredUniverseIds;
use Seat\Notifications\Notifications\AbstractMailNotification;
use Seat\Notifications\Traits\NotificationTools;

/**
 * Class Killmail.
 *
 * @package Seat\Notifications\Notifications\Characters
 */
class Killmail extends AbstractMailNotification implements ExposesRequiredUniverseIds
{
    use NotificationTools;

    /**
     * @var \Seat\Eveapi\Models\Killmails\KillmailDetail
     */
    private $killmail;

    /**
     * Create a new notification instance.
     *
     * @param  \Seat\Eveapi\Models\Killmails\KillmailDetail  $killmail
     */
    public function __construct(KillmailDetail $killmail)
    {

        $this->killmail = $killmail;
    }

    public function middleware(): array
    {
        return array_merge(
            parent::middleware(),
            [new LoadRequiredUniverseIds]
        );
    }

    public function getRequiredUniverseIds(): Collection
    {
        $ids = collect();

        $ids->push($this->killmail->victim->character_id);
        $ids->push($this->killmail->victim->corporation_id);
        $ids = $ids->merge($this->killmail->attackers->pluck('character_id'));
        $ids = $ids->merge($this->killmail->attackers->pluck('corporation_id'));

        return $ids->unique();
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        return (new MailMessage)
            ->subject('Killmail Notification')
            ->line(
                'A new killmail has been recorded!'
            )
            ->line(
                'Lost a ' .
                $this->killmail->victim->ship->typeName . ' in ' .
                $this->killmail->solar_system->name . ' (' .
                number_format($this->killmail->solar_system->security, 2) . ')'
            )
            ->action(
                'Check it out on zKillboard',
                sprintf('https://zkillboard.com/kill/%d/', $this->killmail->killmail_id)
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {

        return [
            'characterName' => $this->killmail->attacker->character->name,
            'corporationName' => $this->killmail->attacker->corporation->name,
            'typeName' => $this->killmail->victim->ship->typeName,
            'system' => $this->killmail->solar_system->name,
            'security' => $this->killmail->solar_system->security,
        ];
    }
}
