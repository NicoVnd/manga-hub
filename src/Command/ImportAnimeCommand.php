<?php
namespace App\Command;

use App\Service\AnimeImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-anime', description: 'Importe des animes depuis Jikan')]
class ImportAnimeCommand extends Command
{
    public function __construct(private AnimeImporter $importer) { parent::__construct(); }

    protected function configure(): void
    {
        $this->addArgument('limit', InputArgument::OPTIONAL, 'Nombre à importer', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int)$input->getArgument('limit');

        $io->title("Import Jikan ($limit)");
        $n = $this->importer->importFromJikan($limit);
        $io->success("Import terminé: $n éléments.");
        return Command::SUCCESS;
    }
}
