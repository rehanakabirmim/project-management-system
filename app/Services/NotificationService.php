<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function create($userId, $title, $message, $type = 'general')
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => 0,
        ]);

        return $notification;
    }
}
