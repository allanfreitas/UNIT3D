<?php

namespace App\Repositories;

use App\PrivateMessage;
use App\User;
use Illuminate\Database\Eloquent\Model;

class TaggedUserRepository
{
    /**
     * Enables various debugging options:
     *
     * 1. Allows you to tag yourself while testing and debugging
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * @var User
     */
    private $user;

    /**
     * @var PrivateMessage
     */
    private $message;

    public function __construct(User $user, PrivateMessage $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * @param $users array|User An array of user objects OR a single user object
     * @param $subject
     * @param $message
     * @return bool
     */
    public function messageUsers($users, $subject, $message)
    {
        // Array of User objects
        if (is_array($users)) {
            foreach ($users as $user) {
                if ($this->validate($user)) {
                    $this->message->create([
                        'sender_id' => 1,
                        'reciever_id' => $user->id,
                        'subject' => $subject,
                        'message' => $message
                    ]);
                }
            }

            return true;
        }

        // A single User object
        if ($this->validate($users)) {
            $this->message->create([
                'sender_id' => 1,
                'reciever_id' => $users->id,
                'subject' => $subject,
                'message' => $message
            ]);
        }
        return true;
    }

    /**
     * @param string $content
     * @param string $subject
     * @param string $message
     */
    public function messageTaggedUsers(string $content, string $subject, string $message)
    {
        preg_match_all('/@[a-zA-Z0-9-_]+/m', $content, $tagged);

        foreach ($tagged[0] as $username) {
            $tagged_user = $this->user->where('username', str_replace('@', '', $username))->first();
            $this->messageUsers($tagged_user, $subject, $message);

        }

        return true;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    protected function validate($user)
    {
        if (!$this->debug || $user->id === auth()->user()->id) {
            return false;
        }

        return true;
    }
}