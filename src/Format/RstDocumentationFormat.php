<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Format;

use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Parser;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use function in_array;
use function preg_match;
use const PHP_EOL;

final class RstDocumentationFormat implements DocumentationFormat
{
    private Parser $rstParser;
    private PhpParser $phpParser;

    public function __construct(?Parser $parser = null)
    {
        $this->phpParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->rstParser = $parser ?: new Parser();
    }

    /** @psalm-return list<non-empty-string> */
    public function supportedExtensions() : array
    {
        return ['rst'];
    }

    public function canHandle(SplFileInfo $file) : bool
    {
        return in_array($file->getExtension(), $this->supportedExtensions(), true);
    }

    public function __invoke(SplFileInfo $file, OutputInterface $output) : bool
    {
        try {
            $documentation = $this->rstParser->parse($file->getContents());
        } catch (Throwable $e) {
            $output->writeln(PHP_EOL . '<error>Error parsing file: ' . $file->getRealPath() . '</error>');
            $output->writeln($e->getMessage() . PHP_EOL);

            return false;
        }

        try {
            foreach ($documentation->getNodes() as $node) {
                if (! ($node instanceof CodeNode) || $node->getLanguage() !== 'php') {
                    continue;
                }

                // TODO: abstract logic into a separated helper class?
                if (! preg_match('/\<\?php/i', $node->getValueString())) {
                    $this->phpParser->parse('<?php ' . PHP_EOL . $node->getValueString());
                    continue;
                }

                $this->phpParser->parse($node->getValueString());
            }
        } catch (Throwable $e) {
            $output->writeln('<error>Wrong code on file: ' . $file->getRealPath() . '</error>');
            $output->writeln($e->getMessage() . PHP_EOL);

            if (isset($node) && $node instanceof CodeNode) {
                $output->writeln($node->getValueString());
            }

            return false;
        }

        return true;
    }
}
