<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\RegistrationTokens;
use Kreait\Firebase\Messaging\MulticastSendReport;

class SendFCMNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $firebaseToken;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($firebaseToken, $data = [])
    {
        $this->firebaseToken = $firebaseToken;
        // $this->data = $data;

        $this->data['title'] = $data['title'] ?? 'no title';
        $this->data['body'] = $data['body'] ?? 'no description';
        $this->data['path'] = $data['path'] ?? 'none';
        $this->data['image'] = $data['image'] ?? asset('images/logo-color.png');
        $this->data['type'] = $data['type'] ?? 'none';
        $this->data['id'] = $data['id'] ?? 'none';
    }

    /**
     * Execute the job.
     */
    public function handle() : void
    {
        $notification = Notification::create($this->data['title'], $this->data['body'], $this->data['image']);

        $FCMdata = [
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'path' => $this->data['path'],
            'image' => $this->data['image'],
            'type' => $this->data['type'],
            'id' => $this->data['id']
        ];

        $apnsConfig = ApnsConfig::fromArray([
            'payload' => [
                'aps' => [
                    'mutable-content' => 1,
                    'sound' => 'default',
                    'badge' => 0
                ],
                'fcm_options' => [
                    'image' => $this->data['image']
                ]
            ]
        ]);

        $message = CloudMessage::new()
            ->withTarget('token', $this->firebaseToken)
            ->withNotification($notification)
            ->withData($FCMdata)
            ->withApnsConfig($apnsConfig);

        $messaging = app('firebase.messaging');

        $report = $messaging->send($message);

        // dd($report);
    }
}
