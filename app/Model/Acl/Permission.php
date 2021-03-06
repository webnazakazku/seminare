<?php

declare(strict_types=1);

namespace App\Model\Acl;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nettrine\ORM\Entity\Attributes\Id;

/**
 * Entita oprávnění.
 *
 * @ORM\Entity(repositoryClass="PermissionRepository")
 * @ORM\Table(name="permission")
 *
 * @author Michal Májský
 * @author Jan Staněk <jan.stanek@skaut.cz>
 */
class Permission
{
    /**
     * Oprávnění spravovat.
     */
    public const MANAGE = 'manage';

    /**
     * Oprávnění přistupovat.
     */
    public const ACCESS = 'access';

    /**
     * Oprávnění spravovat programy, u kterých je uživatel lektor.
     */
    public const MANAGE_OWN_PROGRAMS = 'manage_own_programs';

    /**
     * Oprávnění spravovat všechny programy.
     */
    public const MANAGE_ALL_PROGRAMS = 'manage_all_programs';

    /**
     * Oprávnění spravovat harmonogram.
     */
    public const MANAGE_SCHEDULE = 'manage_schedule';

    /**
     * Oprávnění spravovat místnosti.
     */
    public const MANAGE_ROOMS = 'manage_rooms';

    /**
     * Oprávnění spravovat kategorie bloků.
     */
    public const MANAGE_CATEGORIES = 'manage_categories';

    /**
     * Oprávnění přihlašovat se na programy.
     */
    public const CHOOSE_PROGRAMS = 'choose_programs';

    /** @var string[] */
    public static $permissions = [
        self::MANAGE,
        self::ACCESS,
        self::MANAGE_OWN_PROGRAMS,
        self::MANAGE_ALL_PROGRAMS,
        self::MANAGE_SCHEDULE,
        self::CHOOSE_PROGRAMS,
    ];
    use Id;

    /**
     * Název oprávnění.
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $name;

    /**
     * Role s tímto oprávněním.
     *
     * @ORM\ManyToMany(targetEntity="\App\Model\Acl\Role", mappedBy="permissions", cascade={"persist"})
     *
     * @var Collection|Role[]
     */
    protected $roles;

    /**
     * Prostředek oprávnění.
     *
     * @ORM\ManyToOne(targetEntity="SrsResource", inversedBy="permissions", cascade={"persist"})
     *
     * @var SrsResource
     */
    protected $resource;

    public function __construct(string $name, SrsResource $resource)
    {
        $this->name     = $name;
        $this->resource = $resource;
        $this->roles    = new ArrayCollection();
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return Collection|Role[]
     */
    public function getRoles() : Collection
    {
        return $this->roles;
    }

    public function getResource() : SrsResource
    {
        return $this->resource;
    }
}
