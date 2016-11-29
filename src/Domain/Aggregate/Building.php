<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIntoBuilding;
use Building\Domain\DomainEvent\UserCheckedOutOfBuilding;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $users = [];

    public static function new(string $name) : self
    {
        $self = new self();

        $self->recordThat(NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username)
    {
        if (in_array($username, $this->users)) {
            throw new \RuntimeException("user already checked in");
        }
        $this->recordThat(UserCheckedIntoBuilding::occur(
            (string)$this->uuid,
            [
                'username' => $username
            ]
        ));
    }

    public function checkOutUser(string $username)
    {
        if (!in_array($username, $this->users)) {
            throw new \RuntimeException("user is not checked in.");
        }
        $this->recordThat(UserCheckedOutOfBuilding::occur(
            (string)$this->uuid,
            [
                'username' => $username
            ]
        ));
    }

    public function whenUserCheckedIntoBuilding(UserCheckedIntoBuilding $event)
    {
        $this->users[] = $event->username();
    }

    public function whenUserCheckedOutOfBuilding(UserCheckedOutOfBuilding $event)
    {
        $username = $event->username();
        $this->users = array_filter($this->users, function($arrayValue) use ($username) {
           return $arrayValue !== $username;
        });
    }

    /**
     * @param NewBuildingWasRegistered $event
     */
    public function whenNewBuildingWasRegistered(NewBuildingWasRegistered $event)
    {
        $this->uuid = $event->uuid();
        $this->name = $event->name();
        $this->users = [];
    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }

    /**
     * {@inheritDoc}
     */
    public function id() : string
    {
        return $this->aggregateId();
    }
}
