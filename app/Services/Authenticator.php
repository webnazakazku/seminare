<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Acl\Role;
use App\Model\Acl\RoleRepository;
use App\Model\User\User;
use App\Model\User\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\ORMException;
use Exception;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Security as NS;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use stdClass;

/**
 * Služba starající se o autentizaci uživatelů.
 *
 * @author Michal Májský
 * @author Jan Staněk <jan.stanek@skaut.cz>
 * @author Petr Parolek <petr.parolek@webnazakazku.cz>
 */
class Authenticator implements IAuthenticator
{

    use Nette\SmartObject;

    /**
     * @var NS\Passwords
     */
    private $passwords;

    /** @var Cache */
    private $userRolesCache;

    /** @var UserRepository */
    private $userRepository;

    /** @var RoleRepository */
    private $roleRepository;

    /** @var FilesService */
    private $filesService;

    public function __construct(
            NS\Passwords $passwords,
            UserRepository $userRepository,
            RoleRepository $roleRepository,
            FilesService $filesService,
            IStorage $storage
    )
    {
        $this->passwords = $passwords;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->filesService = $filesService;

        $this->userRolesCache = new Cache($storage, 'UserRoles');
    }

    /**
     * Autentizuje uživatele a případně vytvoří nového.
     *
     * @param string[] $credentials
     *
     * @throws ORMException
     * @throws Exception
     */
    public function authenticate(array $credentials): IIdentity
    {
        list($email, $password) = $credentials;

        /**
         * @var User $user
         */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new Nette\Security\AuthenticationException('The email is incorrect.', self::IDENTITY_NOT_FOUND);
        } elseif (!$this->passwords->verify($password, $user->getPassword())) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        } elseif ($this->passwords->needsRehash($user->getPassword())) {
            //$data = Nette\Utils\ArrayHash::from(["password" => $password]);
            //$this->userService->updateUserBy(['id' => $user->getId()], $data);
            bdump('needsrehash');
        }

        $netteRoles = [];
        if ($user->isApproved()) {
            foreach ($user->getRoles() as $role) {
                $netteRoles[$role->getId()] = $role->getName();
            }
        } else {
            $roleUnapproved = $this->roleRepository->findBySystemName(Role::UNAPPROVED);
            $netteRoles[$roleUnapproved->getId()] = $roleUnapproved->getName();
        }

        return new Identity($user->getId(), $netteRoles, [
            'username' => $user->getUsername()
        ]);
    }

    /**
     * Aktualizuje role přihlášeného uživatele.
     */
    public function updateRoles(NS\User $user, ?Role $testedRole = null): void
    {
        $dbuser = $this->userRepository->findById($user->id);

        $netteRoles = [];

        if (!$testedRole) {
            if ($dbuser->isApproved()) {
                foreach ($dbuser->getRoles() as $role) {
                    $netteRoles[$role->getId()] = $role->getName();
                }
            } else {
                $roleUnapproved = $this->roleRepository->findBySystemName(Role::UNAPPROVED);
                $netteRoles[$roleUnapproved->getId()] = $roleUnapproved->getName();
            }
        } else {
            $netteRoles[0] = Role::TEST;
            $netteRoles[$testedRole->getId()] = $testedRole->getName();
        }

        /** @var Identity $identity */
        $identity = $user->identity;
        $identity->setRoles($netteRoles);
    }

}
