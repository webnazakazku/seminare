<?php
declare(strict_types=1);

namespace App\Model\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Kdyby\Doctrine\EntityRepository;


/**
 * Třída spravující přihlášky.
 *
 * @author Jan Staněk <jan.stanek@skaut.cz>
 */
class ApplicationRepository extends EntityRepository
{
    /**
     * Vrací přihlášku podle id.
     * @param $id
     * @return Application|null
     */
    public function findById($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * Vrací přihlášky podle id, které mají společné všechny verze přihlášky.
     * @param $id
     * @return Collection|Application[]
     */
    public function findByApplicationId(int $id): Collection
    {
        $result = $this->findBy(['applicationId' => $id]);
        return new ArrayCollection($result);
    }

    /**
     * Uloží přihlášku.
     * @param Application $application
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Application $application)
    {
        $this->_em->persist($application);
        $this->_em->flush();
    }

    /**
     * Odstraní přihlášku.
     * @param Application $application
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(Application $application)
    {
        $this->_em->remove($application);
        $this->_em->flush();
    }
}
