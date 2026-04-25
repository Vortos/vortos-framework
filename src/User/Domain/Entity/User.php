<?php

namespace App\User\Domain\Entity;

use App\User\Domain\Event\UserCreatedEvent;
use App\User\Infrastructure\Repository\UserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
// use Vortos\Domain\AggregateRootInterface;
// use Vortos\Domain\AggregateRootTrait;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;
use Vortos\Auth\Attribute\AuthenticatableUser;
use Vortos\Domain\Aggregate\AggregateRoot;
use Vortos\Domain\Identity\AggregateId;

#[Entity(UserRepository::class)]
#[Table(name: 'users')]
#[AuthenticatableUser()]
class User extends AggregateRoot
{

    // use AggregateRootTrait;

    private function __construct(
        #[Id]
        #[Column(name: 'id', type: 'uuid')]
        #[GeneratedValue(strategy: 'NONE')]
        private UserId $id,

        #[Column(name: 'name', type: 'string')]
        private string $name,

        #[Column(name: 'email', type: 'string')]
        private string $email,

        #[Column(name: 'status', type: 'boolean', nullable: true)]
        private ?bool $status,

        #[Column(name: 'version', type: 'integer')]
        private int $version = 0,

        private string $passwordHash = '',
        private array $roles = ['ROLE_USER'],
    ) {}

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function updatePasswordHash(string $hash): void
    {
        $this->passwordHash = $hash;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    // public static function registerUser(string $name, string $email, ?bool $status): self
    // {
    //     $id = UserId::generate();

    //     $user = new User(
    //         $id,
    //         $name,
    //         $email,
    //         $status
    //     );

    //     $user->recordEvent(
    //         new UserCreatedEvent(
    //             $user->id->toString(),
    //             $user->name,
    //             $user->email
    //         )
    //     );

    //     return $user;
    // }

    // Also needed — static factory that matches your registerUser:
    public static function registerUser(string $name, string $email, string $passwordHash = '', array $roles = ['ROLE_USER']): self
    {
        $user = new self(UserId::generate(), $name, $email, null);
        $user->passwordHash = $passwordHash;
        $user->roles = $roles;
        $user->recordEvent(new UserCreatedEvent(
            $user->id->toString(),
            $user->name,
            $user->email,
        ));
        return $user;
    }

    // Reconstruct from DB row — needed by fromRow()
    public static function reconstruct(UserId $id, string $name, string $email, string $passwordHash, array $roles, int $version): self
    {
        $user = new self($id, $name, $email, null);
        $user->passwordHash = $passwordHash;
        $user->roles = $roles;
        // Restore version — reflection needed since it's private on AggregateRoot
        $versionProp = new \ReflectionProperty(\Vortos\Domain\Aggregate\AggregateRoot::class, 'version');
        $versionProp->setAccessible(true);
        $versionProp->setValue($user, $version);
        return $user;
    }

    public function getId(): AggregateId
    {
        return $this->id;
    }

    // public function setId(int $id): void
    // {
    //     $this->id = $id;
    // }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
