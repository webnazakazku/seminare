<?php

declare(strict_types=1);

namespace App\Services;

use Contributte\Console\Application;
use DateTimeImmutable;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Exception;
use MySQLDump;
use mysqli;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\DI\Container;
use Nettrine\ORM\EntityManagerDecorator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

/**
 * Služba pro správu databáze.
 *
 * @author Jan Staněk <jan.stanek@skaut.cz>
 * @author Petr Parolek <petr.parolek@webnazakazku.cz>
 */
class DatabaseService
{
    use Nette\SmartObject;

    /** @var Container */
    public $container;

    /** @var string */
    public $dir;

    /** @var Cache */
    protected $databaseCache;

    /** @var Application */
    private $consoleApplication;

    /** @var EntityManagerDecorator */
    private $em;

    public function __construct(
        string $dir,
        Container $container,
        IStorage $storage,
        Application $consoleApplication,
        EntityManagerDecorator $em
    ) {
        $this->dir       = $dir;
        $this->container = $container;

        $this->databaseCache      = new Cache($storage, 'Database');
        $this->consoleApplication = $consoleApplication;
        $this->em                 = $em;
    }

    /**
     * Vytvoří zálohu databáze a spustí migrace. Spouští se pouze pokud není v cache záznam o provedeném update.
     *
     * @throws Throwable
     */
    public function update() : void
    {
        if ($this->databaseCache->load('updated') === null) {
            $this->databaseCache->save('lock', function () {
                if ($this->databaseCache->load('updated') === null) {
                    $this->databaseCache->save('updated', new DateTimeImmutable());

                    $this->backup();

                    $input  = new ArrayInput([
                        'command' => 'migrations:migrate',
                        '--no-interaction' => true,
                    ]);
                    $output = new BufferedOutput();

                    $this->consoleApplication->add(new MigrateCommand());
                    $this->consoleApplication->setAutoExit(false);
                    $this->consoleApplication->run($input, $output);
                }

                return true;
            });
        }
    }

    /**
     * Vytvoří zálohu databáze.
     *
     * @throws Exception
     */
    public function backup() : void
    {
        $host     = $this->em->getConnection()->getHost();
        $user     = $this->em->getConnection()->getUsername();
        $password = $this->em->getConnection()->getPassword();
        $dbname   = $this->em->getConnection()->getDatabase();

        $dump = new MySQLDump(new mysqli($host, $user, $password, $dbname));

        $timestamp = (new DateTimeImmutable())->format('YmdHi');

        $dump->save($this->dir . '/backup/' . $dbname . '-' . $timestamp . '.sql.gz');
    }
}
