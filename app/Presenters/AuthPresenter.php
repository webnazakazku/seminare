<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Components\Forms;
use App\Model\Mailing\Template;
use App\Model\Mailing\TemplateVariable;
use App\Model\Settings\Settings;
use App\Model\Settings\SettingsException;
use App\Model\User\UserRepository;
use App\Services\MailService;
use App\Services\SettingsService;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Throwable;
use Ublaboo\Mailing\Exception\MailingMailCreationException;
use WebLoader\Nette\CssLoader;
use WebLoader\Nette\JavaScriptLoader;
use function strpos;

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

    /** @persistent */
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
    protected function createComponentCss(): CssLoader
    {
        return $this->webLoader->createCssLoader('auth');
    }

    /**
     * Načte javascript podle konfigurace v common.neon.
     */
    protected function createComponentJs(): JavaScriptLoader
    {
        return $this->webLoader->createJavaScriptLoader('auth');
    }

    /**
     * Sign-in form factory.
     */
    protected function createComponentSignInForm(): Form
    {
        return $this->signInFactory->create(function (): void {
                    $this->restoreRequest($this->backlink);
                    $this->redirect(':Web:Page:');
                });
    }

    public function actionLogout()
    {
        $this->user->logout();
        $this->presenter->flashMessage('Odhlášení proběhlo úspěšně', 'success');
        $this->redirect('Auth:login');
    }

}
