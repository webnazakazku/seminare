<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\AdminModule\Components\IRolesGridControlFactory;
use App\AdminModule\Components\RolesGridControl;
use App\AdminModule\Forms\AddRoleFormFactory;
use App\AdminModule\Forms\EditRoleFormFactory;
use App\Model\Acl\Permission;
use App\Model\Acl\SrsResource;
use App\Services\Authenticator;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use stdClass;
use Throwable;

/**
 * Presenter obsluhující správu rolí.
 *
 * @author Michal Májský
 * @author Jan Staněk <jan.stanek@skaut.cz>
 */
class AclPresenter extends AdminBasePresenter
{
    /** @var string */
    protected $resource = SrsResource::ACL;

    /**
     * @var AddRoleFormFactory
     * @inject
     */
    public $addRoleFormFactory;

    /**
     * @var EditRoleFormFactory
     * @inject
     */
    public $editRoleFormFactory;

    /**
     * @var IRolesGridControlFactory
     * @inject
     */
    public $rolesGridControlFactory;

    /**
     * @var Authenticator
     * @inject
     */
    public $authenticator;

    /**
     * @throws AbortException
     */
    public function startup() : void
    {
        parent::startup();

        $this->checkPermission(Permission::MANAGE);
    }

    public function renderEdit(int $id) : void
    {
        $role = $this->roleRepository->findById($id);

        $this->template->editedRole = $role;
    }

    /**
     * Zapne testování role.
     *
     * @throws AbortException
     */
    public function actionTest(int $id) : void
    {
        $role = $this->roleRepository->findById($id);

        $this->authenticator->updateRoles($this->getPresenter()->user, $role);

        $this->redirect(':Web:Page:default');
    }

    protected function createComponentRolesGrid() : RolesGridControl
    {
        return $this->rolesGridControlFactory->create();
    }

    /**
     * @throws Throwable
     */
    protected function createComponentAddRoleForm() : Form
    {
        $form = $this->addRoleFormFactory->create();

        $form->onSuccess[] = function (Form $form, stdClass $values) : void {
            if ($form->isSubmitted() === $form['cancel']) {
                $this->redirect('Acl:default');
            }

            $this->flashMessage('admin.acl.roles_saved', 'success');

            $id = $this->roleRepository->findLastId();
            $this->redirect('Acl:edit', ['id' => $id]);
        };

        return $form;
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    protected function createComponentEditRoleForm() : Form
    {
        $form = $this->editRoleFormFactory->create((int) $this->getParameter('id'));

        $form->onSuccess[] = function (Form $form, stdClass $values) : void {
            if ($form->isSubmitted() === $form['cancel']) {
                $this->redirect('Acl:default');
            }

            $this->flashMessage('admin.acl.roles_saved', 'success');

            if ($form->isSubmitted() === $form['submitAndContinue']) {
                $id = $values->id;
                $this->redirect('Acl:edit', ['id' => $id]);
            } else {
                $this->redirect('Acl:default');
            }
        };

        return $form;
    }
}
