<?php
namespace Building\Domain\Command;

use Prooph\Common\Messaging\Command;
use Rhumsaa\Uuid\Uuid;

final class CheckInUser extends Command
{
    /**
     * @var string the users username
     */
    private $username;

    /***
     * @var Uuid uuid of the building hte user checked in
     */
    private $building;

    /**
     * CheckUserIn constructor.
     * @param $username string
     * @param $building Uuid
     */
    public function __construct($username, Uuid $building)
    {
        $this->init();

        $this->username = $username;
        $this->building = $building;
    }

    public function username()
    {
        return $this->username;
    }

    public function buildingId() : Uuid
    {
        return $this->building;
    }

    /**
     * Return message payload as array
     *
     * The payload should only contain scalar types and sub arrays.
     * The payload is normally passed to json_encode to persist the message or
     * push it into a message queue.
     *
     * @return array
     */
    public function payload()
    {
        return [
            'username' => $this->username,
            'buildingId' => $this->building->toString()
        ];
    }

    /**
     * This method is called when message is instantiated named constructor fromArray
     *
     * @param array $payload
     * @return void
     */
    protected function setPayload(array $payload)
    {
        $this->username = $payload['username'];
        $this->building = Uuid::fromString($payload['buildingId']);
    }
}