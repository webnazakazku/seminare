<?php

declare(strict_types=1);

namespace App\WebModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;

/**
 * BaseFormFactory pro WebModule.
 *
 * @author Jan Staněk <jan.stanek@skaut.cz>
 */
class BaseFormFactory
{
    use Nette\SmartObject;

    /** @var ITranslator */
    private $translator;

    public function __construct(ITranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Vytvoří formulář.
     */
    public function create() : Form
    {
        $form = new Form();
        $form->setTranslator($this->translator);

        $renderer                                   = new Bs4FormRenderer();
        $renderer->wrappers['control']['container'] = 'div class="col-md-9"';
        $renderer->wrappers['label']['container']   = 'div class="col-md-3 col-form-label"';

        $form->setRenderer($renderer);

        return $form;
    }
}
