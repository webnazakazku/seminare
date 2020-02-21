<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Enums\PaymentType;
use App\Model\User\User;
use Nette;
use Nette\Localization\ITranslator;

/**
 * Služba pro správu uživatelů.
 *
 * @author Jan Staněk <jan.stanek@skaut.cz>
 */
class UserService
{

    use Nette\SmartObject;

    /** @var ITranslator */
    private $translator;

    public function __construct(ITranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Vrací platební metodu uživatele.
     */
    public function getPaymentMethod(User $user): ?string
    {
        $paymentMethod = null;

        foreach ($user->getNotCanceledApplications() as $application) {
            $currentPaymentMethod = $application->getPaymentMethod();
            if ($currentPaymentMethod) {
                if (!$paymentMethod) {
                    $paymentMethod = $currentPaymentMethod;
                } elseif ($paymentMethod !== $currentPaymentMethod) {
                    return PaymentType::MIXED;
                }
            }
        }

        if ($paymentMethod) {
            return $paymentMethod;
        }

        return null;
    }

}
