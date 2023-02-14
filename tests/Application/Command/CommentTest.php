<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   03.02.20
 */

namespace Test\Application\Command;

use Helper\DIContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use Simtt\Domain\Model\LogEntry;
use Simtt\Infrastructure\Prompter\Prompter;
use Simtt\Infrastructure\Service\LogFile;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystemTrait;

class CommentTest extends TestCase
{
    use VirtualFileSystemTrait;

    protected function getCommandShortName(): string
    {
        return 'comment.cmd';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFileSystem();
        DIContainer::$container->set('prompter', Prompter::create());
    }

    public function testEmptyLog(): void
    {
        $output = $this->runCommand('comment "Comment"');
        self::assertSame('No entries found for today', rtrim($output->fetch()));
    }

    public function testCommentSimple(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
        ]);

        $output = $this->runCommand('comment "Comment"');
        self::assertSame("Comment 'Comment' updated for log started at 10:00 for 'task #2'", rtrim($output->fetch()));
    }

    public function testCommentOutOfRange(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
        ]);

        $output = $this->runCommand('comment 2 "Comment"');
        self::assertSame('Offset 2 is out of range for today', rtrim($output->fetch()));
    }

    #[DataProvider('provideComment')]
    /**
     * @param int    $offset
     * @param int    $index
     * @param string $expectedMessage
     */
    public function testComment(int $offset, int $index, string $expectedMessage): void
    {
        /** @var LogEntry[] $entries */
        $entries = [
            LogEntryCreator::create('900', '', 'task #1'),
            LogEntryCreator::create('1000', ''),
            LogEntryCreator::create('1050', '', 'task #1', 'comment'),
            LogEntryCreator::create('1130', '12:00', 'task #3'),
        ];
        LogEntryCreator::setUpLogFileToday($entries);
        /** @noinspection PhpUnhandledExceptionInspection */
        $comment = base64_encode(random_bytes(8));
        $stringInput = $offset === 0
            ? "comment \"$comment\""
            : "comment $offset \"$comment\"";
        $output = $this->runCommand($stringInput);
        self::assertSame(sprintf($expectedMessage, $comment), rtrim($output->fetch()));

        $entries[$index]->comment = $comment;
        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        self::assertStringEqualsFile($logFile->getFile(), implode("\n", $entries) . "\n");
    }

    public static function provideComment(): array
    {
        return [
            [0, 3, "Comment '%s' updated for log started at 11:30 for 'task #3'"],
            [1, 2, "Comment '%s' updated for log started at 10:50 for 'task #1'"],
            [2, 1, "Comment '%s' updated for log started at 10:00"],
            [3, 0, "Comment '%s' updated for log started at 09:00 for 'task #1'"],
        ];
    }

    public function testSetEmptyComment(): void
    {
        $entries = [
            LogEntryCreator::create('900', '', 'task #1'),
            LogEntryCreator::create('1000', '', 'task #2'),
        ];
        LogEntryCreator::setUpLogFileToday($entries);

        $output = $this->runCommand('comment 1 ""');
        self::assertSame("Comment '' updated for log started at 09:00 for 'task #1'", rtrim($output->fetch()));

        $entries[1]->comment = '';
        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        self::assertStringEqualsFile($logFile->getFile(), implode("\n", $entries) . "\n");
    }
}
