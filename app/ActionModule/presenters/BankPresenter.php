<?php

declare(strict_types=1);

namespace App\ActionModule\Presenters;

use App\Model\Settings\Settings;
use App\Model\Settings\SettingsException;
use App\Model\Settings\SettingsFacade;
use App\Services\BankService;
use Nette\Application\Responses\TextResponse;

/**
 * Presenter obsluhující načítání plateb z API banky.
 *
 * @author Jan Staněk <jan.stanek@skaut.cz>
 */
class BankPresenter extends ActionBasePresenter
{
    /**
     * @var BankService
     * @inject
     */
    public $bankService;

    /**
     * @var SettingsFacade
     * @inject
     */
    public $settingsFacade;


    /**
     * Zkontroluje splatnost přihlášek.
     * @throws SettingsException
     * @throws \Throwable
     */
    public function actionCheck() : void
    {
        $from = $this->settingsFacade->getDateValue(Settings::BANK_DOWNLOAD_FROM);
        $this->bankService->downloadTransactions($from);

        $response = new TextResponse(null);
        $this->sendResponse($response);
    }
}
