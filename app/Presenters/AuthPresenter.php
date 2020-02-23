<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Components\Forms;
use App\Model\User\UserRepository;
use App\Services\MailService;
use App\Services\SettingsService;
use Nette\Application\UI\Form;
use WebLoader\Nette\CssLoader;
use WebLoader\Nette\JavaScriptLoader;

class AuthPresenter extends BasePresenter
{
    /**
     * @var SettingsService
     * @inject
     */
    public $settingsService;

    /**
     * @var UserRepository
     * @inject
     */
    public $userRepository;

    /**
     * @var MailService
     * @inject
     */
    public $mailService;

    /**
     * @var string
     * @persistent
     */
    public $backlink = '';

    /** @var Forms\SignInFormFactory */
    private $signInFactory;

    public function __construct(Forms\SignInFormFactory $signInFactory)
    {
        $this->signInFactory = $signInFactory;
    }

    /**
     * Načte css podle konfigurace v common.neon.
     */
    protected function createComponentCss() : CssLoader
    {
        return $this->webLoader->createCssLoader('auth');
    }

    /**
     * Načte javascript podle konfigurace v common.neon.
     */
    protected function createComponentJs() : JavaScriptLoader
    {
        return $this->webLoader->createJavaScriptLoader('auth');
    }

    /**
     * Sign-in form factory.
     */
    protected function createComponentSignInForm() : Form
    {
        return $this->signInFactory->create(function () : void {
                    $this->restoreRequest($this->backlink);
                    $this->redirect(':Web:Page:');
        });
    }

    public function actionLogout() : void
    {
        $this->user->logout();
        $this->presenter->flashMessage('Odhlášení proběhlo úspěšně', 'success');
        $this->redirect('Auth:login');
    }
}
