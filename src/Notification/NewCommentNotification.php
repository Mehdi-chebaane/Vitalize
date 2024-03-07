<?php

namespace App\Notification;



use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class NewCommentNotification extends Notification
{
    public function __construct()
    {
        parent::__construct('New comment on your publication');
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        return ['email'];
    }
}