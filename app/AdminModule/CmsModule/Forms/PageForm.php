<?php

declare(strict_types=1);

namespace App\AdminModule\CmsModule\Forms;

use App\AdminModule\Forms\BaseFormFactory;
use App\Model\Acl\RoleRepository;
use App\Model\Cms\Content\CapacitiesContent;
use App\Model\Cms\Content\Content;
use App\Model\Cms\Content\DocumentContent;
use App\Model\Cms\Content\ImageContent;
use App\Model\Cms\Content\UsersContent;
use App\Model\Cms\Document\TagRepository;
use App\Model\Cms\Page;
use App\Model\Cms\PageException;
use App\Model\Cms\PageRepository;
use App\Services\AclService;
use App\Services\CmsService;
use App\Services\FilesService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use stdClass;
use function get_class;
use function ucfirst;

/**
 * Komponenta s formulářem pro úpravu obsahu stránky.
 *
 * @author Michal Májský
 * @author Jan Staněk <jan.stanek@skaut.cz>
 * @author Petr Parolek <petr.parolek@webnazakazku.cz>
 */
class PageForm extends UI\Control
{
    /**
     * Id upravované stránky.
     *
     * @var int
     */
    public $id;

    /**
     * Upravovaná stránka.
     *
     * @var Page
     */
    private $page;

    /**
     * Upravovaná oblast.
     *
     * @var string
     */
    public $area;

    /**
     * Událost při uložení formuláře.
     *
     * @var callable[]
     */
    public $onPageSave;

    /**
     * Událost při chybě ukládání stránky.
     *
     * @var callable[]
     */
    public $onPageSaveError;

    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var PageRepository */
    private $pageRepository;

    /** @var AclService */
    private $aclService;

    /** @var CmsService */
    private $cmsService;

    /** @var RoleRepository */
    private $roleRepository;

    /** @var TagRepository */
    private $tagRepository;

    /** @var FilesService */
    private $filesService;

    public function __construct(
        int $id,
        string $area,
        BaseFormFactory $baseFormFactory,
        PageRepository $pageRepository,
        AclService $aclService,
        CmsService $cmsService,
        RoleRepository $roleRepository,
        TagRepository $tagRepository,
        FilesService $filesService
    ) {
        $this->id   = $id;
        $this->area = $area;

        $this->baseFormFactory = $baseFormFactory;
        $this->pageRepository  = $pageRepository;
        $this->aclService      = $aclService;
        $this->cmsService      = $cmsService;
        $this->roleRepository  = $roleRepository;
        $this->tagRepository   = $tagRepository;
        $this->filesService    = $filesService;

        $this->page = $this->pageRepository->findById($id);
    }

    /**
     * Vykreslí komponentu.
     *
     * @throws PageException
     */
    public function render() : void
    {
        $this->template->setFile(__DIR__ . '/templates/page_form.latte');

        $this->template->area     = $this->area;
        $this->template->contents = $this->page->getContents($this->area);

        $this->template->render();
    }

    /**
     * Vytvoří komponentu.
     *
     * @throws PageException
     */
    public function createComponentForm() : Form
    {
        $form = $this->baseFormFactory->create();

        $form->addHidden('id')->setDefaultValue($this->page->getId());
        $form->addHidden('area')->setDefaultValue($this->area);
        $form->addSelect('type', 'admin.cms.pages_content_type', $this->prepareContentTypesOptions());

        foreach ($this->page->getContents($this->area) as $content) {
            switch (get_class($content)) {
                case CapacitiesContent::class:
                    $content->injectRoleRepository($this->roleRepository);
                    $content->injectAclService($this->aclService);
                    break;
                case DocumentContent::class:
                    $content->injectTagRepository($this->tagRepository);
                    break;
                case ImageContent::class:
                    $content->injectFilesService($this->filesService);
                    break;
                case UsersContent::class:
                    $content->injectRoleRepository($this->roleRepository);
                    $content->injectAclService($this->aclService);
                    break;
            }

            $form = $content->addContentForm($form);
        }

        $form->addSubmit('submit', 'admin.common.save');
        $form->addSubmit('submitAndContinue', 'admin.common.save_and_continue');
        $form->addSubmit('submitAdd', 'admin.common.add');
        $form->addSubmit('submitMain', 'common.area.main')
            ->setHtmlAttribute('class', 'nav-link');
        $form->addSubmit('submitSidebar', 'common.area.sidebar')
            ->setHtmlAttribute('class', 'nav-link');

        $form->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
        $form->onSuccess[] = [$this, 'processForm'];

        $form->onError[] = function () : void {
            $this->onPageSaveError($this);
        };

        return $form;
    }

    /**
     * Zpracuje formulář.
     *
     * @throws PageException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function processForm(Form $form, stdClass $values) : void
    {
        $page = $this->pageRepository->findById((int) $values->id);

        $area = $values->area;
        $type = $values->type;

        foreach ($page->getContents($area) as $content) {
            $inputName     = $content->getContentFormName();
            $formContainer = $values->$inputName;
            if ($formContainer['delete']) {
                $this->cmsService->removeContent($content);
            } else {
                $content->contentFormSucceeded($form, $values);
                $this->cmsService->saveContent($content);
            }
        }

        if ($form->isSubmitted() === $form['submitAdd']) {
            $contentClass = '\\App\\Model\\Cms\\Content\\' . ucfirst($type) . 'Content';
            $content      = new $contentClass($page, $area);
            $content->setHeading($form->getTranslator()->translate('common.content.default_heading.' . $type));
            $this->cmsService->saveContent($content);
        }

        if ($form->isSubmitted() === $form['submitAdd']) {
            $submitName = 'submitAdd';
        } elseif ($form->isSubmitted() === $form['submitMain']) {
            $submitName = 'submitMain';
        } elseif ($form->isSubmitted() === $form['submitSidebar']) {
            $submitName = 'submitSidebar';
        } elseif ($form->isSubmitted() === $form['submitAndContinue']) {
            $submitName = 'submitAndContinue';
        } else {
            $submitName = 'submit';
        }

        $this->onPageSave($this, $submitName);
    }

    /**
     * Připraví možnosti obsahů stránky pro select.
     *
     * @return string[]
     */
    private function prepareContentTypesOptions() : array
    {
        $options = [];
        foreach (Content::$types as $type) {
            $options[$type] = 'common.content.name.' . $type;
        }

        return $options;
    }
}
