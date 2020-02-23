<?php

declare(strict_types=1);

namespace App\Model\Cms;

use App\Model\Cms\Content\ContentDto;

class PageDto
{
    /**
     * Název stránky.
     *
     * @var string
     */
    protected $name;

    /**
     * Cesta stránky.
     *
     * @var string
     */
    protected $slug;

    /**
     * Role, které mají na stránku přístup.
     *
     * @var string[]
     */
    protected $allowedRoles;

    /**
     * Obsahy v hlavní části stránky.
     *
     * @var ContentDto[]
     */
    protected $mainContents;

    /**
     * @param string[]     $allowedRoles
     * @param ContentDto[] $mainContents
     */
    public function __construct(string $name, string $slug, array $allowedRoles, array $mainContents)
    {
        $this->name         = $name;
        $this->slug         = $slug;
        $this->allowedRoles = $allowedRoles;
        $this->mainContents = $mainContents;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getSlug() : string
    {
        return $this->slug;
    }

    /**
     * @return string[]
     */
    public function getAllowedRoles() : array
    {
        return $this->allowedRoles;
    }

    /**
     * @return ContentDto[]
     */
    public function getMainContents() : array
    {
        return $this->mainContents;
    }

    /**
     * Je stránka viditelná pro uživatele v rolích?
     *
     * @param string[] $userRoles
     */
    public function isAllowedForRoles(array $userRoles) : bool
    {
        foreach ($userRoles as $userRole) {
            foreach ($this->allowedRoles as $allowedRole) {
                if ($userRole === $allowedRole) {
                    return true;
                }
            }
        }

        return false;
    }
}
