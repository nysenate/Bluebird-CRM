<?php

namespace Civi\CompilePlugin;

use Civi\CompilePlugin\Command\CompileListCommand;
use Civi\CompilePlugin\Event\CompileEvents;
use Civi\CompilePlugin\Subscriber\OldTaskAdapter;
use Civi\CompilePlugin\Subscriber\ShellSubscriber;
use Civi\CompilePlugin\Util\ComposerPassthru;
use Civi\CompilePlugin\Util\TaskUIHelper;
use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class CompilePlugin implements PluginInterface, EventSubscriberInterface, Capable
{

    /**
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Composer\IO\IOInterface
     */
    private $io;

    /**
     * @var EventSubscriberInterface[]
     */
    private $extraSubscribers;

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_INSTALL_CMD => ['validateMode', -5],
            ScriptEvents::PRE_UPDATE_CMD => ['validateMode', -5],
            ScriptEvents::POST_INSTALL_CMD => ['runTasks', -5],
            ScriptEvents::POST_UPDATE_CMD => ['runTasks', -5],
        ];
    }

    public function getCapabilities()
    {
        return [
            'Composer\Plugin\Capability\CommandProvider' => CommandProvider::class,
        ];
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $dispatch = $composer->getEventDispatcher();
        $this->extraSubscribers = [
            'oldTask' => new OldTaskAdapter(),
        ];
        foreach ($this->extraSubscribers as $subscriber) {
            $dispatch->addSubscriber($subscriber);
        }
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // NOTE: This method is only valid on composer v2.
        $dispatch = $composer->getEventDispatcher();
        // This looks asymmetrical, but the meaning: "remove all listeners which involve the given object".
        foreach ($this->extraSubscribers as $subscriber) {
            $dispatch->removeListener($subscriber);
        }
        $this->extraSubscribers = null;
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // NOTE: This method is only valid on composer v2.
    }

    /**
     * The "prompt" compilation mode only makes sense with interactive usage.
     */
    public function validateMode(Event $event)
    {
        if (!class_exists('Civi\CompilePlugin\TaskRunner')) {
            // Likely a problem in composer v1 uninstall process?
            return;
        }
        $taskRunner = new TaskRunner($this->composer, $this->io);
        if ($taskRunner->getMode() === 'prompt' && !$this->io->isInteractive()) {
            $this->io->write(file_get_contents(__DIR__ . '/messages/cannot-prompt.txt'));
        }
    }

    public function runTasks(Event $event)
    {
        $io = $event->getIO();

        if (!class_exists('Civi\CompilePlugin\TaskList')) {
            // Likely a problem in composer v1 uninstall process?
            $io->write("<warning>Skip CompilePlugin::runTasks. Environment does not appear well-formed.</warning>");
            return;
        }

        // We need to propagate some of our process's options to the subprocess...

        // The "soft" options should be safer for passing options between different versions.
        // The "soft" options will be used if recognized by the recipient, and ignored otherwise.
        // Ex: $soft['o']['dry-run'] = true;
        $soft = [];
        $softEsc = $soft ? '--soft-options=' . escapeshellarg(base64_encode(json_encode($soft))) : '';

        $runner = new ComposerPassthru($event->getComposer(), $io);
        $runner->run("@composer compile $softEsc");
    }
}
